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

class BitcoinKeyHierarchical
{
    /**
     * @var EccAdapter
     */
    private $adapter;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var int
     */
    private $parent_fp;

    /**
     * @var int
     */
    private $sequence;

    /**
     * @var string
     */
    private $chain_code;

    /**
     * @var KeyInterface
     */
    private $key;

    /**
     * @param EccAdapter $adapter
     * @param int $depth
     * @param int $parent_fp
     * @param int $sequence
     * @param string $chain_code
     * @param PublicKey $key
     * @throws \Exception
     */
    public function __construct(
        BitcoinEccAdapter $adapter,
        $depth,
        $parent_fp,
        $sequence,
        $chain_code,
        BitcoinKeyPublic $key
    ) {
        if (!$key->isCompressed()) {
            throw new \InvalidArgumentException('A HierarchicalKey must always be compressed');
        }

        $this->adapter = $adapter;
        $this->depth = $depth;
        $this->sequence = $sequence;
        $this->parent_fp = $parent_fp;
        $this->chain_code = $chain_code;
        $this->key = $key;
    }


    /**
     * Return the depth of this key. This is limited to 256 sequential derivations.
     *
     * @return int
     */
    public function getDepth()
    {
        return (int) $this->depth;
    }


    /**
     * Get the sequence number for this address. Hardened keys are
     * created with sequence > 0x80000000. a sequence number lower
     * than this can be derived with the public key.
     *
     * @return int
     */
    public function getSequence()
    {
        return (int) $this->sequence;
    }


    /**
     * Get the fingerprint of the parent key. For master keys, this is 00000000.
     *
     * @return int
     */
    public function getFingerprint()
    {
        if ($this->getDepth() === 0) {
            return 0;
        }
        
        return (int) $this->parent_fp;
    }


    /**
     * Return the fingerprint to be used for child keys.
     * @return string
     */
    public function getChildFingerprint()
    {
        return $this->getPublicKey()->getPubKeyHash()->slice(0, 4)->int();
    }


    /**
     * Return the chain code - a deterministic 'salt' for HMAC-SHA512
     * in child derivations
     *
     * @return string
     */
    public function getChainCode()
    {
        return $this->chain_code;
    }


    /**
     * Get the public key the private key or public key.
     *
     * @return PublicKey
     */
    public function getPublicKey()
    {
        return $this->key;
    }


    /**
     * Returns data to be hashed to yield the child offset
     *
     * @param int $sequence
     * @return string
     * @throws \Exception
     */
    public function getHmacSeed($sequence)
    {
        $buffer = MathBuffer::fromInt($this->adapter->getMath(), $sequence, 4);
        
        return $this->key->buffer() . $buffer->binary();
    }


    /**
     * Derive a child key
     *
     * @param int $sequence
     * @return HierarchicalKey
     * @throws \Exception
     */
    public function deriveChild($sequence)
    {
        $math = $this->adapter->getMath();

        $hfunc = 'hash_hmac';

        $hash = new MathBuffer($math, $hfunc('sha512', $this->getHmacSeed($sequence), $this->getChainCode(), true));
        $offset = $hash->slice(0, 32);
        $chain = $hash->slice(32);
        
        $key = $this->getPublicKey();

        return new BitcoinKeyHierarchical(
            $this->adapter,
            $this->getDepth() + 1,
            $this->getChildFingerprint(),
            $sequence,
            $chain->binary(),
            $key->tweakAdd($offset->int())
        );
    }
}
