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

class EccPrimitivesPoint
{
    /**
     * @var CurveFp
     */
    private $curve;

    /**
     * @var MathAdapter
     */
    private $adapter;

    /**
     * @var ModularArithmetic
     */
    private $mod_adapter;

    /**
     * @var string
     */
    private $x;

    /**
     * @var string
     */
    private $y;

    /**
     * @var string
     */
    private $order;

    /**
     * @var bool
     */
    private $infinity = false;


    /**
     * Initialize a new instance
     *
     * @param MathAdapter $adapter
     * @param CurveFp $curve
     * @param int|string $x
     * @param int|string $y
     * @param int|string $order
     * @param bool $infinity
     *
     * @throws \RuntimeException when either the curve does not contain the given coordinates or when order is not null and P(x, y) * order is not equal to infinity.
     */
    public function __construct(
        MathAdapter $adapter,
        EccPrimitivesFp $curve,
        $x,
        $y,
        $order,
        $infinity = false
    ) {
        $this->adapter    = $adapter;
        $this->mod_adapter = $curve->getModAdapter();
        $this->curve      = $curve;
        $this->x          = (string) $x;
        $this->y          = (string) $y;
        $this->order      = $order !== null ? (string) $order : '0';
        $this->infinity   = (bool) $infinity;
    }


    /**
     * @return MathAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }


    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Point::isInfinity()
     */
    public function isInfinity()
    {
        return (bool) $this->infinity;
    }


    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Point::getCurve()
     */
    public function getCurve()
    {
        return $this->curve;
    }


    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Point::getOrder()
     */
    public function getOrder()
    {
        return (string) $this->order;
    }


    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Point::getX()
     */
    public function getX()
    {
        return $this->x;
    }


    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Point::getY()
     */
    public function getY()
    {
        return $this->y;
    }


    /**
     * Adds another point to the current one and returns the resulting point.
     *
     * @param Point $addend
     * @return Point
     */
    public function add(EccPrimitivesPoint $addend)
    {
        if (! $this->curve->equals($addend->getCurve())) {
            throw new \RuntimeException("The Elliptic Curves do not match.");
        }

        if ($addend->isInfinity()) {
            return clone $this;
        }

        if ($this->isInfinity()) {
            return clone $addend;
        }

        $math = $this->adapter;
        $modMath = $this->mod_adapter;
        $prime = $this->getCurve()->getPrime();

        if ($math->cmp($addend->getX(), $this->x) == 0) {
            if ($math->cmp($addend->getY(), $this->y) == 0) {
                return $this->getDouble();
            } else {
                return new self($this->adapter, $this->curve, 0, 0, 0, true);
            }
        }

        $slope = $modMath->mul(
            $math->sub($addend->getY(), $this->getY()),
            $math->inverseMod($math->sub($addend->getX(), $this->getX()), $prime)
        );

        $xR = $modMath->sub(
            $math->sub($math->pow($slope, 2), $this->x),
            $addend->getX()
        );

        $yR = $modMath->sub(
            $math->mul($slope, $math->sub($this->x, $xR)),
            $this->y
        );

        if ($math->cmp(0, $yR) > 0) {
            $yR = $math->add($prime, $yR);
        }
        
        return new self($this->adapter, $this->curve, $xR, $yR, $this->order, false);
    }


    /**
     * Compares the current instance to another point.
     *
     * @param Point $other
     * @return int|string
     */
    public function cmp(EccPrimitivesPoint $other)
    {
        if ($other->isInfinity() && $this->isInfinity()) {
            return 0;
        }

        if ($other->isInfinity() || $this->isInfinity()) {
            return 1;
        }

        $math = $this->adapter;
        $equal = ($math->cmp($this->x, $other->getX()) == 0);
        $equal &= ($math->cmp($this->y, $other->getY()) == 0);
        $equal &= $this->isInfinity() == $other->isInfinity();
        $equal &= $this->curve->equals($other->getCurve());

        return $equal ? 0 : 1;
    }


    /**
     * Checks whether the current instance is equal to the given point.
     *
     * @param Point $other
     * @return bool true when points are equal, false otherwise.
     */
    public function equals(EccPrimitivesPoint $other)
    {
        return $this->cmp($other) == 0;
    }


    public function mul($n)
    {
        $math = $this->adapter;
        
        if ($this->isInfinity()) {
            return $this->curve->getInfinity();
        }

        if ($math->cmp($this->order, '0') > 0) {
            $n = $math->mod($n, $this->order);
        }

        if ($math->cmp($n, '0') == 0) {
            return $this->curve->getInfinity();
        }

        if ($math->cmp($n, '0') > 0) {
            $n3 = $math->mul(3, $n);

            $negative_self = new self(
                $math,
                $this->getCurve(),
                $this->getX(),
                $math->sub(0, $this->getY()),
                $this->getOrder()
            );

            $i = $math->div($math->leftMostBit($n3), 2);

            $result = clone $this;

            while ($math->cmp($i, 1) > 0) {
                $result = $result->getDouble();

                if ($math->cmp($math->bitwiseAnd($n3, $i), '0') != 0
                    && $math->cmp($math->bitwiseAnd($n, $i), '0') == 0
                ) {
                    $result = $result->add($this);
                }

                if ($math->cmp($math->bitwiseAnd($n3, $i), 0) == 0 && $math->cmp($math->bitwiseAnd($n, $i), 0) != 0) {
                    $result = $result->add($negative_self);
                }

                $i = $math->div($i, 2);
            }

            return $result;
        }
    }


    /**
     *
     */
    private function validate()
    {
        if (! $this->infinity && ! $this->curve->contains($this->x, $this->y)) {
            throw new \RuntimeException('Invalid point');
        }
    }


    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Point::getDouble()
     */
    public function getDouble()
    {
        if ($this->isInfinity()) {
            return $this->curve->getInfinity();
        }

        $math = $this->adapter;
        $modMath = $this->mod_adapter;
        $prime = $this->getCurve()->getPrime();

        $a = $this->curve->getA();
        $threeX2 = $math->mul(3, $math->pow($this->x, 2));

        $tangent  = $modMath->div(
            $math->add($threeX2, $a),
            $math->mul(2, $this->y)
        );

        $x3 = $modMath->sub(
            $math->pow($tangent, 2),
            $math->mul(2, $this->x)
        );

        $y3 = $modMath->sub(
            $math->mul($tangent, $math->sub($this->x, $x3)),
            $this->y
        );

        if ($math->cmp(0, $y3) > 0) {
            $y3 = $math->strval($math->add($prime, $y3));
        }

        return new self($this->adapter, $this->curve, $x3, $y3, $this->order);
    }
}
