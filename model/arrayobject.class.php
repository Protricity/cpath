<?php
namespace CPath\Model;

use CPath\Interfaces\IArrayObject;

/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 6/25/13
 * Time: 12:13 AM
 * To change this template use File | Settings | File Templates.
 */
abstract class ArrayObject implements IArrayObject {

    /**
     * Return a reference to this object's associative array
     * @return array the associative array
     */
    abstract protected function &getArray();

    /**
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getData(0, 'key') gets $data[0]['key'];
     * @return mixed the data array or targeted data specified by path
     * @throws \InvalidArgumentException if the data path doesn't exist
     */
    function &getDataPath($_path=NULL) {
        if($_path === NULL)
            return $this->getArray();
        $data =& $this->getArray();
        foreach(func_get_args() as $arg) {
            if(!is_array($data) || !isset($data[$arg]))
                throw new \InvalidArgumentException("Invalid data path at '{$arg}': " . implode('.', func_get_args()));
            $data = &$data[$arg];
        }
        return $data;
    }

    /**
     * Remove an element from an array and return its value
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->pluck(0, 'key') removes $data[0]['key'] and returns it's value;
     * @return mixed the data array or targeted data specified by path
     * @throws \InvalidArgumentException if the data path doesn't exist
     */
    public function pluck($_path) {
        $data =& $this->getArray();
        $args = func_get_args();
        $last = array_pop($args);
        foreach($args as $arg) {
            if(!is_array($data) || !isset($data[$arg]))
                throw new \InvalidArgumentException("Invalid data path at '{$arg}': " . implode('.', func_get_args()));
            $data = &$data[$arg];
        }
        if(empty($data[$last]) && $data[$last] !== NULL)
            throw new \InvalidArgumentException("Path '" . implode('.', func_get_args()) . "' is not set");
        $value = $data[$last];
        unset($data[$last]);
        return $value;
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
    public function offsetExists($offset) {
        $data = &$this->getArray();
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
        $data = &$this->getArray();
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
        $data = &$this->getArray();
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
        $data = &$this->getArray();
        unset($data[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->getArray());
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count() {
        return count($this->getArray());
    }

    // Statics

}