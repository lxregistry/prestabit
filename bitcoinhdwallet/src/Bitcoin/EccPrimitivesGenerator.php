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

require_once 'EccPrimitivesPoint.php';

class EccPrimitivesGenerator extends EccPrimitivesPoint
{
    /**
     * @param MathAdapter   $adapter
     * @param CurveFp       $curve
     * @param int|string    $x
     * @param int|string    $y
     * @param null          $order
     */
    public function __construct(MathAdapter $adapter, EccPrimitivesFp $curve, $x, $y, $order = null)
    {
        parent::__construct($adapter, $curve, $x, $y, $order);
    }
}
