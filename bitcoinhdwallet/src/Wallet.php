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

require_once 'Bitcoin/BitcoinEccAdapter.php';
require_once 'Bitcoin/BitcoinKeyFactory.php';

class Wallet
{
    public static function isValidFirstAddress($lx_first_address)
    {
        $address = self::getNewBitcoinAddress(0);

        if ($address == $lx_first_address) {
            return true;
        }

        return false;
    }


    public static function getNewBitcoinAddress($nb)
    {
        $adapter = BitcoinEccAdapter::instance();
        $xpub = Configuration::get('LX_EXTENDED_PUBLIC_KEY');

        if (!$xpub) {
            return false;
        }

        return BitcoinKeyFactory::extend($xpub, $adapter)
            ->deriveChild(0)
            ->deriveChild($nb)
            ->getPublicKey()
            ->getAddress(0);
    }
}
