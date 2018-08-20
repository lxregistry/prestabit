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

require_once 'MathTheory.php';

abstract class MathAdapter
{
    /**
     * Base58Chars
     *
     * This is a string containing the allowed characters in base58.
     */
    private static $base58chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';


    /**
     * Encode a given hex string in base58
     *
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function encode($data)
    {
        $size = $this->strlen($data);

        if ($data === '') {
            return '';
        }

        $orig = $data;
        $decimal = $this->base2dec($data, 256);
        $return = '';

        while ($this->cmp($decimal, 0) > 0) {
            list($decimal, $rem) = $this->divQr($decimal, 58);
            $return .= self::$base58chars[$rem];
        }

        $return = strrev($return);

        //leading zeros
        for ($i = 0; $i < $size && $orig[$i] === "\x0"; $i++) {
            $return = '1' . $return;
        }

        return $return;
    }


    /**
     * Encode the given data in base58, with a checksum to check integrity.
     *
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function encodeCheck($data)
    {
        return $this->encode($data . $this->checksum($data));
    }


    /**
     * Base58 Decode
     *
     * This function accepts a base58 encoded string, and decodes the
     * string into a number, which is converted to hexadecimal. It is then
     * padded with zero's.
     *
     * @param $base58
     * @return string
     */
    public function decode($base58)
    {
        $original = $base58;
        $length = $this->strlen($base58);
        $return = '0';

        for ($i = 0; $i < $length; $i++) {
            $return = $this->add($this->mul($return, 58), strpos(self::$base58chars, $base58[$i]));
        }

        $binary = $this->cmp($return, '0') === 0 ? '' : $this->hex2bin($this->decHex($return));

        for ($i = 0; $i < $length && $original[$i] === '1'; $i++) {
            $binary = "\x00" . $binary;
        }

        return $binary;
    }


    /**
     * Decode a base58 checksum string and validate checksum
     *
     * @param string $base58
     * @return string
     */
    public function decodeCheck($base58)
    {
        $binary = $this->decode($base58);

        $data = $this->substr($binary, 0, -4);
        $cs_verify = $this->substr($binary, -4);

        return ($this->checksum($data) !== $cs_verify ? '' : $data);
    }


    /**
     * Calculate a checksum for the given data
     *
     * @param string $data
     * @return string
     */
    public function checksum($data)
    {
        return $this->substr($this->hash256($data), 0, 4);
    }


    /**
     * Base58 Decode Checksum
     *
     * Returns the original hex data that was encoded in base58 check format.
     *
     * @param string $base58
     * @return string
     */
    public function base58DecodeChecksum($base58)
    {
        $hex = $this->decode($base58);
        
        return $this->substr($hex, 2, $this->strlen($hex) - 10);
    }


    /**
     * Hash256
     *
     * Takes a sha256(sha256()) hash of the $string. Intended only for
     * hex strings, as it is packed into raw bytes.
     *
     * @param string $data
     * @return string
     */
    public function hash256($data)
    {
        return hash("sha256", hash("sha256", $data, true), true);
    }


    /**
     * Hash160
     *
     * Takes $data as input and returns a ripemd160(sha256()) hash of $string.
     * Intended for only hex strings, as it is packed into raw bytes.
     *
     * @param string $string
     * @return string
     */
    public function hash160($string)
    {
        return hash('ripemd160', hash('sha256', $string, true), true);
    }


    public static function digits($base)
    {
        if ($base > 64) {
            $digits = '';
            
            for ($loop = 0; $loop < 256; $loop++) {
                $digits .= chr($loop);
            }
        } else {
            $digits = '0123456789abcdefghijklmnopqrstuvwxyz';
            $digits .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
        }

        $digits = self::substr($digits, 0, $base);

        return (string)$digits;
    }


    public function hex($number, $pad = 0)
    {
        $hex = $this->dec2base($number, 16);

        return str_pad('', 2 * $pad - $this->strlen($hex), '0') . $hex;
    }


    public function hex2bin($data)
    {
        if (!function_exists('hex2bin')) {
            $sbin = '';
            $len = $this->strlen($data);
            
            for ($i = 0; $i < $len; $i += 2) {
                $sbin .= pack('H*', $this->substr($data, $i, 2));
            }

            return $sbin;
        }
        
        return hex2bin($data);
    }


    /**
     * @param $integer
     * @return bool
     */
    public function isEven($integer)
    {
        return $this->cmp($this->mod($integer, 2), 0) === 0;
    }


    /**
     * Similar to gmp_div_qr, return a tuple containing the
     * result and the remainder
     *
     * @param $dividend
     * @param int $divisor
     * @return array
     */
    public function divQr($dividend, $divisor)
    {
        $div = $this->div($dividend, $divisor);
        $remainder = $this->sub($dividend, $this->mul($div, $divisor));
        
        return array($div, $remainder);
    }


    /**
     * @return NumberTheory
     */
    public function numberTheory()
    {
        return new MathTheory($this);
    }


    public static function strlen($str)
    {
        return Tools::strlen($str, self::encoding());
    }


    public static function substr($str, $start, $length = false)
    {
        return Tools::substr(
            $str,
            $start,
            ($length === false ? self::strlen($str) : (int) $length),
            self::encoding()
        );
    }


    public static function encoding()
    {
        return 'iso-8859-1';
    }


    /**
     * Adds two numbers
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    abstract public function add($a, $b);


    /**
     * Substract one number from another
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    abstract public function sub($a, $b);


    /**
     * Multiplies a number by another.
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    abstract public function mul($a, $b);


    /**
     * Divides a number by another.
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    abstract public function div($a, $b);


    /**
     * Returns the remainder of a division
     *
     * @param int|string $a
     * @param int|string $b
     * @return int|string
     */
    abstract public function mod($a, $b);


    /**
     * Raises a number to a power.
     *
     * @param int|string $base The number to raise.
     * @param int|string $exponent The power to raise the number to.
     * @return int|string
     */
    abstract public function pow($base, $exponent);


    /**
     *
     * @param int|string $a
     * @param int|string $m
     */
    abstract public function inverseMod($a, $m);


    /**
     * Calculates the modular exponent of a number.
     *
     * @param int|string $base
     * @param int|string $exponent
     * @param int|string $modulus
     */
    abstract public function powMod($base, $exponent, $modulus);


    /**
     * Returns the string representation of a returned value.
     *
     * @param int|string $number
     */
    abstract public function strval($number, $base = 10);


    /**
     * Compares two numbers
     *
     * @param int|string $a
     * @param int|string $b
     * @return int less than 0 if first is less than second, 0 if equal, greater than 0 if greater than.
     */
    abstract public function cmp($a, $b);


    abstract public function dec2base($number, $base);


    abstract public function base2dec($number, $base);


    /**
     * Converts an hexadecimal string to decimal.
     *
     * @param string $hex
     * @return int|string
     */
    abstract public function hexDec($hex);


    /**
     * Converts a decimal string to hexadecimal.
     *
     * @param int|string $dec
     * @return int|string
     */
    abstract public function decHex($dec);


    /**
     * Computes Jacobi symbol
     *
     * @param int|string $a
     * @param int|string $n
     */
    abstract public function jacobi($a, $n);


    /**
     * Checks whether a number is a prime.
     *
     * @param int|string $n
     * @return boolean
     */
    abstract public function isPrime($n);


    /**
     * Gets the next known prime that is greater than a given prime.
     *
     * @param int|string $starting_value
     * @return int|string
     */
    abstract public function nextPrime($starting_value);


    /**
     * Performs a logical AND between two values.
     *
     * @param int|string $first
     * @param int|string $other
     * @return int|string
     */
    abstract public function bitwiseAnd($first, $other);


    /**
     * Performs a logical XOR between two values.
     *
     * @param int|string $first
     * @param int|string $other
     * @return int|string
     */
    abstract public function bitwiseXor($first, $other);


    /**
     * Shifts bits to the right
     * @param int|string $number Number to shift
     * @param int|string $positions Number of positions to shift
     */
    abstract public function rightShift($number, $positions);


    /**
     * Shifts bits to the left
     * @param int|string $number Number to shift
     * @param int|string $positions Number of positions to shift
     */
    abstract public function leftShift($number, $positions);


    abstract public function leftMostBit($number);
}
