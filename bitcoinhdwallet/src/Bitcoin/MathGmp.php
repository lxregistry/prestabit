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

if (!function_exists('gmp_strval')) {
    function gmp_strval()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_add()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_sub()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_mul()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_div()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_div_r()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_cmp()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_pow()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_init()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_mod()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_intval()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_powm()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_invert()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_jacobi()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_prob_prime()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_nextprime()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_and()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }

    function gmp_xor()
    {
        throw new RuntimeException('Please install GMP PHP Extension.');
    }
}


class MathGmp extends MathAdapter
{
    /**
     * Adds two numbers
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    public function add($a, $b)
    {
        return gmp_strval(gmp_add($a, $b));
    }


    /**
     * Substract one number from another
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    public function sub($a, $b)
    {
        return gmp_strval(gmp_sub($a, $b));
    }


    /**
     * Multiplies a number by another.
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    public function mul($a, $b)
    {
        return gmp_strval(gmp_mul($a, $b));
    }


    /**
     * Divides a number by another.
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    public function div($a, $b)
    {
        return gmp_strval(gmp_div($a, $b));
    }


    /**
     * Returns the remainder of a division
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    public function mod($a, $b)
    {
        $res = gmp_div_r($a, $b);

        if (gmp_cmp(0, $res) > 0) {
            $res = gmp_add($b, $res);
        }

        return gmp_strval($res);
    }


    /**
     * Raises a number to a power.
     *
     * @param int|string $base The number to raise.
     * @param int|string $exponent The power to raise the number to.
     * @return int|string
     */
    public function pow($base, $exponent)
    {
        return gmp_strval(gmp_pow(gmp_init($base, 10), $exponent));
    }


    /**
     * Returns the string representation of a returned value.
     *
     * @param int|string $number
     */
    public function strval($number, $base = 10)
    {
        return gmp_strval($number, $base);
    }


    /**
     * Compares two numbers
     *
     * @param int|string $a
     * @param int|string $b
     * @return int less than 0 if first is less than second, 0 if equal, greater than 0 if greater than.
     */
    public function cmp($a, $b)
    {
        return gmp_cmp($a, $b);
    }


    public function dec2base($number, $base)
    {
        $digits = self::digits($base);
        $value = '';

        $number = gmp_init($number);
        $base = gmp_init($base);

        while (gmp_cmp($number, gmp_sub($base, '1')) > 0) {
            $rest = gmp_mod($number, $base);
            $number = gmp_div($number, $base);
            $value = $digits[gmp_intval($rest)] . $value;
        }

        $value = $digits[gmp_intval($number)] . $value;
        
        return $value;
    }


    public function base2dec($number, $base)
    {
        $digits = self::digits($base);
        
        if ($base < 37) {
            $number = Tools::strtolower($number);
        }

        $size = $this->strlen($number);
        $dec = '0';
        
        for ($loop = 0; $loop < $size; $loop++) {
            $element = strpos($digits, $number[$loop]);
            $power = gmp_pow(gmp_init($base), $size - $loop - 1);
            $dec = gmp_add($dec, gmp_mul($element, $power));
        }

        return gmp_strval($dec);
    }


    /**
     * Converts an hexadecimal string to decimal.
     *
     * @param string $hex
     * @return int|string
     */
    public function hexDec($hex)
    {
        return gmp_strval(gmp_init($hex, 16), 10);
    }


    /**
     * Converts a decimal string to hexadecimal.
     *
     * @param int|string $dec
     * @return int|string
     */
    public function decHex($dec)
    {
        $hex = gmp_strval(gmp_init($dec, 10), 16);

        if ($this->strlen($hex) % 2 != 0) {
            $hex = '0' . $hex;
        }

        return $hex;
    }


    /**
     * Calculates the modular exponent of a number.
     *
     * @param int|string $base
     * @param int|string $exponent
     * @param int|string $modulus
     */
    public function powMod($base, $exponent, $modulus)
    {
        return gmp_strval(gmp_powm($base, $exponent, $modulus));
    }


    /**
     *
     * @param int|string $a
     * @param int|string $m
     */
    public function inverseMod($a, $m)
    {
        return gmp_strval(gmp_invert(gmp_init($a, 10), gmp_init($m, 10)));
    }


    /**
     * Computes Jacobi symbol
     *
     * @param int|string $a
     * @param int|string $n
     */
    public function jacobi($a, $n)
    {
        return gmp_strval(gmp_jacobi($a, $n));
    }


    /**
     * Checks whether a number is a prime.
     *
     * @param int|string $n
     * @return boolean
     */
    public function isPrime($n)
    {
        return gmp_prob_prime($n);
    }


    /**
     * Gets the next known prime that is greater than a given prime.
     *
     * @param int|string $starting_value
     * @return int|string
     */
    public function nextPrime($starting_value)
    {
        return gmp_strval(gmp_nextprime($starting_value));
    }


    /**
     * Performs a logical AND between two values.
     *
     * @param int|string $first
     * @param int|string $other
     * @return int|string
     */
    public function bitwiseAnd($first, $other)
    {
        return gmp_strval(gmp_and(gmp_init($first, 10), gmp_init($other, 10)));
    }


    /**
     * Performs a logical XOR between two values.
     *
     * @param int|string $first
     * @param int|string $other
     * @return int|string
     */
    public function bitwiseXor($first, $other)
    {
        return gmp_strval(gmp_xor(gmp_init($first, 10), gmp_init($other, 10)));
    }


    /**
     * Shifts bits to the right
     * @param int|string $number    Number to shift
     * @param int|string $positions Number of positions to shift
     */
    public function rightShift($number, $positions)
    {
        // Shift 1 right = div / 2
        return gmp_strval(gmp_div($number, gmp_pow(2, $positions)));
    }


    /**
     * Shifts bits to the left
     * @param int|string $number    Number to shift
     * @param int|string $positions Number of positions to shift
     */
    public function leftShift($number, $positions)
    {
        // Shift 1 left = mul by 2
        return gmp_strval(gmp_mul(gmp_init($number), gmp_pow(2, $positions)));
    }


    public function leftMostBit($number)
    {
        if (gmp_cmp($number, 0) > 0) {
            $result = 1;

            while (gmp_cmp($result, $number) < 0 || gmp_cmp($result, $number) == 0) {
                $result = gmp_mul(2, $result);
            }

            return gmp_strval(gmp_div($result, 2));
        }
    }
}
