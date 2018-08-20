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

class MathBuffer
{
    /**
     * @var MathAdapter
     */
    protected $adapter;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $buffer;


    /**
     * @param MathAdapter $adapter
     * @param $data
     */
    public function __construct(MathAdapter $adapter, $data, $byte_size = null)
    {
        $this->adapter = $adapter;

        if ($byte_size !== null) {
            if ($this->adapter->cmp($this->adapter->strlen($data), $byte_size) > 0) {
                throw new \Exception('Byte string exceeds maximum size');
            }
        } else {
            $byte_size = $this->adapter->strlen($data);
        }

        $this->size = $byte_size;
        $this->buffer = $data;
    }


    /**
     * @param MathAdapter $adapter
     * @param string $hex_string
     * @param int $byte_size
     * @return Buffer
     * @throws \Exception
     */
    public static function fromHex(MathAdapter $adapter, $hex_string = '', $byte_size = null)
    {
        if ($adapter::strlen($hex_string) > 0 && !ctype_xdigit($hex_string)) {
            throw new \InvalidArgumentException('BufferHex: non-hex character passed: ' . $hex_string);
        }

        $binary = pack("H*", $hex_string);
        
        return new self($adapter, $binary, $byte_size);
    }


    /**
     * @param MathAdapter $adapter
     * @param int|string $integer
     * @param null|int $byte_size
     * @return Buffer
     */
    public static function fromInt(MathAdapter $adapter, $integer, $byte_size = null)
    {
        $binary = pack("H*", $adapter->decHex($integer));
        
        return new self($adapter, $binary, $byte_size);
    }


    /**
     * @param int $start
     * @param int|null $end
     * @return Buffer
     * @throws \Exception
     */
    public function slice($start, $end = null)
    {
        if ($start > $this->size()) {
            throw new \Exception('Start exceeds buffer length');
        }

        if ($end === null) {
            return new self($this->adapter, $this->adapter->substr($this->binary(), $start));
        }

        if ($end > $this->size()) {
            throw new \Exception('Length exceeds buffer length');
        }

        $string = $this->adapter->substr($this->binary(), $start, $end);
        $length = $this->adapter->strlen($string);

        return new self($this->adapter, $string, $length);
    }


    /**
     * @return int
     */
    public function size()
    {
        return $this->size;
    }


    /**
     * @return int
     */
    public function internalSize()
    {
        return $this->adapter->strlen($this->buffer);
    }


    /**
     * @return string
     */
    public function binary()
    {
        if ($this->size !== null) {
            if ($this->adapter->strlen($this->buffer) < $this->size) {
                return str_pad($this->buffer, $this->size, chr(0), STR_PAD_LEFT);
            } elseif ($this->adapter->strlen($this->buffer) > $this->size) {
                return $this->adapter->substr($this->buffer, 0, $this->size);
            }
        }
        
        return $this->buffer;
    }


    /**
     * @return string
     */
    public function hex()
    {
        return bin2hex($this->binary());
    }


    /**
     * @return int|string
     */
    public function int()
    {
        return $this->adapter->hexDec($this->hex());
    }
}
