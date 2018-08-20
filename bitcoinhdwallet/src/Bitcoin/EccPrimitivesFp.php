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

require_once 'MathModular.php';
require_once 'EccPrimitivesGenerator.php';

class EccPrimitivesFp
{
    /**
     * Elliptic curve over the field of integers modulo a prime.
     *
     * @var int|string
     */
    protected $a = 0;

    /**
     *
     * @var int|string
     */
    protected $b = 0;

    /**
     *
     * @var int|string
     */
    protected $prime = 0;

    /**
     *
     * @var ModularArithmetic
     */
    protected $modAdapter = null;

    /**
     *
     * @var MathAdapter
     */
    protected $adapter = null;


    /**
     * Constructor that sets up the instance variables.
     *
     * @param $adapter MathAdapter
     * @param $prime int|string
     * @param $a int|string
     * @param $b int|string
     */
    public function __construct(MathAdapter $adapter, $prime, $a, $b)
    {
        $this->a = $a;
        $this->b = $b;
        $this->prime = $prime;
        $this->adapter = $adapter;
        $this->modAdapter = new MathModular($this->adapter, $prime);
    }


    /**
     * Checks whether the curve contains the given coordinates.
     *
     * @param int|string $x
     * @param int|string $y
     * @return bool
     */
    public function contains($x, $y)
    {
        $math = $this->adapter;
        
        $eq_zero = $math->cmp(
            $math->mod(
                $math->sub(
                    $math->pow($y, 2),
                    $math->add($math->add($math->pow($x, 3), $math->mul($this->getA(), $x)), $this->getB())
                ),
                $this->getPrime()
            ),
            0
        );
        
        return ($eq_zero == 0);
    }


    /**
     * Returns the a parameter of the curve.
     *
     * @return int|string
     */
    public function getA()
    {
        return $this->a;
    }


    /**
     * Returns the b parameter of the curve.
     *
     * @return int|string
     */
    public function getB()
    {
        return $this->b;
    }


    /**
     * Returns the point identified by given coordinates.
     *
     * @param int|string $x
     * @param int|string $y
     * @param int|string $order
     * @return Point
     */
    public function getPoint($x, $y, $order = null)
    {
        return new EccPrimitivesPoint($this->adapter, $this, $x, $y, $order);
    }


    /**
     * Returns a modular arithmetic adapter.
     *
     * @return ModularArithmetic
     */
    public function getModAdapter()
    {
        return $this->modAdapter;
    }


    /**
     * Returns a point representing infinity on the curve.
     *
     * @return PointInterface
     */
    public function getInfinity()
    {
        return new EccPrimitivesPoint($this->adapter, $this, 0, 0, 0, true);
    }


    /**
     * Returns the prime associated with the curve.
     *
     * @return int|string
     */
    public function getPrime()
    {
        return $this->prime;
    }


    /**
     * Compares the curve to another.
     *
     * @param CurveFp $other
     * @return int < 0 if $this < $other, 0 if $other == $this, > 0 if $this > $other
     */
    public function cmp(EccPrimitivesFp $other)
    {
        $math = $this->adapter;
        
        $equal  = ($math->cmp($this->getA(), $other->getA()) == 0);
        $equal &= ($math->cmp($this->getB(), $other->getB()) == 0);
        $equal &= ($math->cmp($this->getPrime(), $other->getPrime()) == 0);
        
        return ($equal) ? 0 : 1;
    }


    /**
     * Checks whether the curve is equal to another.
     *
     * @param CurveFp $other
     * @return bool
     */
    public function equals(EccPrimitivesFp $other)
    {
        return $this->cmp($other) == 0;
    }


    /**
     *
     * @param int|string $x
     * @param int|string $y
     * @param string $order
     * @return GeneratorPoint
     */
    public function getGenerator($x, $y, $order = null)
    {
        return new EccPrimitivesGenerator($this->adapter, $this, $x, $y, $order);
    }
}
