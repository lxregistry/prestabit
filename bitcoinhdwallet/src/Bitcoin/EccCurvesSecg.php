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

require_once 'EccPrimitivesFp.php';

class EccCurvesSecg
{
    private $adapter;

    const NAME_SECP_256K1 = 'secp256k1';


    /**
     * @param MathAdapter $adapter
     */
    public function __construct(MathAdapter $adapter)
    {
        $this->adapter = $adapter;
    }


    /**
     * @return \Ecc\Primitives\CurveFp
     */
    public function curve256k1()
    {
        $p = $this->adapter->hexDec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F');
        $a = 0;
        $b = 7;

        return new EccPrimitivesFp($this->adapter, $p, $a, $b);
    }


    /**
     * @return \Ecc\Primitives\GeneratorPoint
     */
    public function generator256k1()
    {
        $order = $this->adapter->hexDec('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141');
        $x = $this->adapter->hexDec('0x79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798');
        $y = $this->adapter->hexDec('0x483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8');

        return $this->curve256k1()->getGenerator($x, $y, $order);
    }
}
