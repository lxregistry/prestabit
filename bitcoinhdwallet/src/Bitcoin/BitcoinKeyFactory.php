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

require_once 'MathBuffer.php';
require_once 'BitcoinKeyHierarchical.php';

class BitcoinKeyFactory
{
    const HD_PUB_MAGIC_BYTES = '0488b21e';

    /**
     * @param string $extended_key
     * @param EccAdapter $adapter
     * @return HierarchicalKey
     */
    public static function extend($extended_key, BitcoinEccAdapter $adapter = null)
    {
        $math = $adapter->getMath();
        $payload = $math->decodeCheck($extended_key);
        $parts = array();

        foreach (array(4, 1, 4, 4, 32, 33) as $position) {
            $parts[] = new MathBuffer($math, $math->substr($payload, 0, $position), $position);
            $payload = $math->substr($payload, $position);
        }

        try {
            list ($bytes, $depth, $parent_fp, $sequence, $chain_code, $key) = $parts;
        } catch (\Exception $e) {
            throw new \ErrorException('Failed to extract HierarchicalKey');
        }

        if ($bytes->hex() !== self::HD_PUB_MAGIC_BYTES) {
            throw new \InvalidArgumentException('Invalid HD key magic bytes');
        }

        $public_key = $adapter->publicKeyFromBuffer($key);

        return new BitcoinKeyHierarchical(
            $adapter,
            $depth->binary(),
            $parent_fp->binary(),
            $sequence->binary(),
            $chain_code->binary(),
            $public_key
        );
    }
}
