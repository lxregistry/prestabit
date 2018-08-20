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

class MathModular
{
    /**
     * @var MathAdapter
     */
    private $adapter;


    /**
     * @var
     */
    private $modulus;


    /**
     * @param MathAdapter $adapter
     * @param $modulus
     */
    public function __construct(MathAdapter $adapter, $modulus)
    {
        $this->adapter = $adapter;
        $this->modulus = $modulus;
    }


    /**
     * @param $augend
     * @param $addend
     * @return int|string
     */
    public function add($augend, $addend)
    {
        return $this->adapter->mod($this->adapter->add($augend, $addend), $this->modulus);
    }


    /**
     * @param $minuend
     * @param $subtrahend
     * @return int|string
     */
    public function sub($minuend, $subtrahend)
    {
        return $this->adapter->mod($this->adapter->sub($minuend, $subtrahend), $this->modulus);
    }


    /**
     * @param $multiplier
     * @param $muliplicand
     * @return int|string
     */
    public function mul($multiplier, $muliplicand)
    {
        return $this->adapter->mod($this->adapter->mul($multiplier, $muliplicand), $this->modulus);
    }


    /**
     * @param $dividend
     * @param $divisor
     * @return int|string
     */
    public function div($dividend, $divisor)
    {
        return $this->adapter->mod(
            $this->adapter->mul($dividend, $this->adapter->inverseMod($divisor, $this->modulus)),
            $this->modulus
        );
    }


    /**
     * @param $base
     * @param $exponent
     * @return mixed
     */
    public function pow($base, $exponent)
    {
        return $this->adapter->powmod($base, $exponent, $this->modulus);
    }
}
