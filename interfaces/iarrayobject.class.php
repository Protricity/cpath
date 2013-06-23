<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IArrayObject extends \ArrayAccess, \IteratorAggregate {

}

trait TArrayAccessHelper {

    abstract public function &getData();

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
    public function offsetExists($offset) {
        $data = &$this->getData();
        return isset($data[$offset]);
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
    public function offsetGet($offset) {
        $data = &$this->getData();
        return $data[$offset];
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
    public function offsetSet($offset, $value) {
        $data = &$this->getData();
        $data[$offset] = $value;
        if (is_null($offset))
            $data[] = $value;
        else
            $data[$offset] = $value;
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
    public function offsetUnset($offset) {
        $data = &$this->getData();
        unset($data[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getData());
    }
}