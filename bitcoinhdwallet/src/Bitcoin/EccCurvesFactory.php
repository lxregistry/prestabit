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

require_once 'EccCurvesSecg.php';

class EccCurvesFactory
{
    /**
     * @param $name
     * @param MathAdapter $adapter
     * @return \Ecc\Primitives\CurveFp
     */
    public static function getCurveByName($name, MathAdapter $adapter)
    {
        $secpFactory = self::getSecpFactory($adapter);

        switch ($name) {
            case EccCurvesSecg::NAME_SECP_256K1:
                return $secpFactory->curve256k1();
            default:
                throw new \RuntimeException('Unknown curve.');
        }
    }


    /**
     * @param $name
     * @param MathAdapter $adapter
     * @return \Mdanter\Ecc\Primitives\GeneratorPoint
     */
    public static function getGeneratorByName($name, MathAdapter $adapter)
    {
        $secpFactory = self::getSecpFactory($adapter);

        switch ($name) {
            case EccCurvesSecg::NAME_SECP_256K1:
                return $secpFactory->generator256k1();
            default:
                throw new \RuntimeException('Unknown generator.');
        }
    }


    /**
     * @param MathAdapter $adapter
     * @return SecgCurve
     */
    private static function getSecpFactory(MathAdapter $adapter)
    {
        return new EccCurvesSecg($adapter);
    }
}
