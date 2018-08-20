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

require_once 'MathAdapter.php';
require_once 'MathGmp.php';
require_once 'EccCurvesFactory.php';
require_once 'BitcoinKeyPublic.php';

class BitcoinEccAdapter
{
    /**
     * @var EccAdapter
     */
    private static $adapter;

    /**
     * @var MathAdapter
     */
    private $math;

    /**
     * @var GeneratorPoint
     */
    private $generator;


    /**
     * @param string $math_name
     * @param string $generator_name
     * @return EccAdapter
     */
    public static function instance()
    {
        if (null === self::$adapter) {
            $math = new MathGmp();

            if (!$math instanceof MathAdapter) {
                throw new \InvalidArgumentException('EccAdapter: Invalid MathAdapter');
            }

            $generator = EccCurvesFactory::getGeneratorByName('secp256k1', $math);

            if (!$generator instanceof EccPrimitivesGenerator) {
                throw new \InvalidArgumentException('EccAdapter: Invalid GeneratorPoint');
            }

            self::$adapter = new self($math, $generator);
        }

        return self::$adapter;
    }


    /**
     * @param MathAdapter $math
     * @param GeneratorPoint $generator
     */
    protected function __construct(MathAdapter $math, EccPrimitivesGenerator $generator)
    {
        $this->math = $math;
        $this->generator = $generator;
    }


    /**
     * @return MathAdapter
     */
    public function getMath()
    {
        return $this->math;
    }


    /**
     * @return GeneratorPoint
     */
    public function getGenerator()
    {
        return $this->generator;
    }


    /**
     * @param Point $point
     * @param bool|false $compressed
     * @return PublicKey
     */
    public function getPublicKey(EccPrimitivesPoint $point, $compressed = false)
    {
        return new BitcoinKeyPublic($this, $point, $compressed);
    }


    /**
     * @param Buffer $buffer
     * @return PublicKey
     * @throws \Exception
     */
    public function publicKeyFromBuffer($buffer)
    {
        if (!in_array(
            $buffer->size(),
            array(BitcoinKeyPublic::LENGTH_COMPRESSED, BitcoinKeyPublic::LENGTH_UNCOMPRESSED),
            true
        )) {
            throw new Exception('Invalid hex string, must match size of compressed or uncompressed public key');
        }

        $compressed = $buffer->size() == BitcoinKeyPublic::LENGTH_COMPRESSED;
        $x_coord = $buffer->slice(1, 32)->int();

        return new BitcoinKeyPublic(
            $this,
            $this->getGenerator()
                ->getCurve()
                ->getPoint(
                    $x_coord,
                    $compressed
                    ? $this->recoverYfromX($x_coord, $buffer->slice(0, 1)->hex())
                    : $buffer->slice(33, 32)->int()
                ),
            $compressed
        );
    }


    /**
     * @param int|string $x_coord
     * @param string $prefix
     * @return int|string
     * @throws \Exception
     */
    public function recoverYfromX($x_coord, $prefix)
    {
        if (!in_array($prefix, array(BitcoinKeyPublic::KEY_COMPRESSED_ODD, BitcoinKeyPublic::KEY_COMPRESSED_EVEN))) {
            throw new RuntimeException('Incorrect byte for a public key');
        }

        $math = $this->math;
        $curve = $this->getGenerator()->getCurve();
        $prime = $curve->getPrime();

        // Calculate first root
        do {
            try {
                $root0 = $math->numberTheory()->squareRootModPrime(
                    $math->add(
                        $math->powMod(
                            $x_coord,
                            3,
                            $prime
                        ),
                        $curve->getB()
                    ),
                    $prime
                );
            } catch (Exception $ex) {
                $root0 = null;
            }
        } while (!$root0);

        return (($prefix == BitcoinKeyPublic::KEY_COMPRESSED_EVEN) == $math->isEven($root0))
            ? $root0
            : $math->sub($prime, $root0);
    }
}
