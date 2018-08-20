<?php
/**
* 2018 LX
*
* NOTICE OF LICENSE
*
*  @author    LX
*  @copyright 2018 LX
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class BitcoinKeyPublic
{
    /**
     * Length of an uncompressed key
     */
    const LENGTH_UNCOMPRESSED = 65;

    /**
     * Length of a compressed key
     */
    const LENGTH_COMPRESSED = 33;

    /**
     * When key is uncompressed, this is the prefix.
     */
    const KEY_UNCOMPRESSED = '04';

    /**
     * When y coordinate is even, prepend x coordinate with this hex byte
     */
    const KEY_COMPRESSED_EVEN = '02';

    /**
     * When y coordinate is odd, prepend x coordinate this this hex byte
     */
    const KEY_COMPRESSED_ODD = '03';

    /**
     * @var EccAdapter
     */
    private $adapter;

    /**
     * @var Point
     */
    private $point;

    /**
     * @var bool
     */
    private $compressed;


    /**
     * @param EccAdapter $adapter
     * @param Point $point
     * @param bool $compressed
     */
    public function __construct(BitcoinEccAdapter $adapter, EccPrimitivesPoint $point, $compressed = false)
    {
        if (false === is_bool($compressed)) {
            throw new \InvalidArgumentException('PublicKey: Compressed must be a boolean');
        }

        $this->adapter = $adapter;
        $this->point = $point;
        $this->compressed = $compressed;
    }


    /**
     * @return Point
     */
    public function getPoint()
    {
        return $this->point;
    }


    /**
     * @param bool $compressed
     * @param Point $point
     * @return string
     */
    public function getPrefix($compressed, EccPrimitivesPoint $point)
    {
        return $compressed
            ? $this->adapter->getMath()->isEven($point->getY())
                ? self::KEY_COMPRESSED_EVEN
                : self::KEY_COMPRESSED_ODD
            : self::KEY_UNCOMPRESSED;
    }


    /**
     * @param int|string $tweak
     * @return PublicKey
     */
    public function tweakAdd($tweak)
    {
        $offset = $this->adapter->getGenerator()->mul($tweak);
        $newPoint = $this->point->add($offset);
        
        return $this->adapter->getPublicKey($newPoint, $this->compressed);
    }


    /**
     * @param int|string $tweak
     * @return PublicKeyInterface
     */
    public function tweakMul($tweak)
    {
        $point = $this->point->mul($tweak);
        return $this->adapter->getPublicKey($point, $this->compressed);
    }


    /**
     * @return bool
     */
    public function isCompressed()
    {
        return $this->compressed;
    }


    /**
     * @return Buffer
     */
    public function getPubKeyHash()
    {
        $math = $this->adapter->getMath();

        return new MathBuffer($math, $math->hash160($this->buffer()));
    }


    /**
     * @return string
     */
    public function buffer()
    {
        $math = $this->adapter->getMath();
        $point = $this->getPoint();
        $compressed = $this->isCompressed();
        
        $parts = array();
        
        $parts[] = MathBuffer::fromInt($math, $this->getPrefix($compressed, $point), 1)->binary();

        if ($compressed) {
            $parts[] = MathBuffer::fromInt($math, $point->getX(), 32)->binary();
        } else {
            $parts[] = MathBuffer::fromInt($math, $point->getX(), 32)->binary();
            $parts[] = MathBuffer::fromInt($math, $point->getY(), 32)->binary();
        }

        return implode('', $parts);
    }


    public function getAddress($segwit = false)
    {
        $math = $this->adapter->getMath();
        $data = $this->buffer();

        if ($segwit) {
            $hash160 = chr(5) . $math->hash160(chr(0) . chr(20) . $math->hash160($data));
        } else {
            $hash160 = chr(0) . $math->hash160($data);
        }

        $address = $hash160 . $math->checksum($hash160);

        return $math->encode($address);
    }
}
