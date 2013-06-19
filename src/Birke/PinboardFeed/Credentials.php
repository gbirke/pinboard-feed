<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gbirke
 * Date: 19.06.13
 * Time: 19:31
 * To change this template use File | Settings | File Templates.
 */

namespace Birke\PinboardFeed;

/**
 * Get credentials from JSON file
 *
 * @package Birke\PinboardFeed
 */
class Credentials implements \ArrayAccess {

    protected $jsondata;

    function __construct($jsonfile)
    {
        if(!file_exists($jsonfile)) {
            throw new \RuntimeException("$jsonfile does not exist.");
        }
        if(!is_readable($jsonfile)) {
            throw new \RuntimeException("Can't read $jsonfile");
        }
        $jsondata = json_decode(file_get_contents($jsonfile), true);
        if(is_null($jsondata)) {
            throw new \RuntimeException("Decoding $jsonfile failed.");
        }
        $this->jsondata = $jsondata;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->jsondata[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->jsondata[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->jsondata[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->jsondata[$offset]);
    }


}