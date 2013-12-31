<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Compare;

class Comparator implements IComparator{

    private $mLastName = NULL;

    /**
     * Compare two variables of any type
     * @param mixed $o1
     * @param mixed $o2
     * @param String|NULL $name the name of the data being compared
     * @return void
     * @throws NotEqualException if the objects were not equal
     */
    function compare($o1, $o2, $name=NULL) {
        if(is_scalar($o1)) {
            if(!is_scalar($o2))
                throw new NotEqualException($this->_name($name) . "Scalars cannot be compared to non-scalars", 1);
            $this->compareScalar($o1, $o2, $name);
        } elseif (is_array($o1)) {
            if(!is_array($o2))
                throw new NotEqualException($this->_name($name) . "Array cannot be compared to non-arrays", 1);
            $this->_add($name);
            $this->compareArray($o1, $o2);
        } else {
            if(($n1 = get_class($o1)) != ($n2 = get_class($o2)))
                throw new NotEqualException($this->_name($name) . "Object type '{$n1}' cannot be compared to type '{$n2}'", strcasecmp($n1, $n2));
            if($o1 instanceof IComparable && $o2 instanceof IComparable) {
                $this->_add($name);
                $this->compareObj($o1, $o2);
            } elseif($o1 != $o2) {
                throw new NotEqualException($this->_name($name) . "Object types '{$n1}' do not appear to be equal", strcasecmp($n1, $n2));
            }
        }
    }

    /**
     * Compare two arrays
     * @param Array $a1
     * @param Array $a2
     * @param String|NULL $_ignoreKeys varargs of keys to ignore
     * @throws NotEqualException if the objects were not equal
     * @return void
     */
    function compareArray(Array $a1, Array $a2, $_ignoreKeys=NULL) {
        if(($s1 = sizeof($a1)) - ($s2 = sizeof($a2)) !== 0)
            throw new NotEqualException($this->_name() . "Arrays do not match in size ($s1 != $s2)", $s1 - $s2);

        if($_ignoreKeys && !is_array($_ignoreKeys))
            $_ignoreKeys = array_slice(func_get_args(), 2);
        else
            $_ignoreKeys = array();

        foreach($a1 as $k1=>$v1) {
            if(in_array($k1, $_ignoreKeys))
                continue;
            if(!array_key_exists($k1, $a2))
                throw new NotEqualException($this->_name() . "Array keys do not match ($k1)", 1);
            $v2 = $a2[$k1];
            $l = $this->mLastName;
            $this->_add($k1);
            $this->compare($v1, $v2);
            $this->mLastName = $l;
        }
    }

    /**
     * Compare two scalars
     * @param mixed $s1
     * @param mixed $s2
     * @param String|NULL $name the name of the scalar data
     * @throws NotEqualException if the objects were not equal
     * @return void
     */
    function compareScalar($s1, $s2, $name=NULL) {
        if($s1 !== $s2)
            throw new NotEqualException($this->_name($name) . "Scalars ($s1) !== ($s2)", $s1 - $s2);
    }

    /**
     * Compare two objects
     * @param \CPath\Compare\IComparable $o1
     * @param \CPath\Compare\IComparable $o2
     * @throws NotEqualException if the objects were not equal
     * @return void
     */
    function compareObj(IComparable $o1, IComparable $o2) {
        $o1->compareTo($o2, $this);
    }

    private function _add($name=NULL) {
        if(!$name)
            return;
        if($this->mLastName)
            $this->mLastName .= '[' . $name . ']';
        else
            $this->mLastName = $name;
    }

    private function _name($name=NULL) {
        if(!$name && $this->mLastName)
            $name = $this->mLastName;
        return $name ? $name . ": " : '';
    }

    // Static

    static function areEqual(IComparable $obj1, IComparable $obj2) {
        $C = new Comparator();
        try {
            $obj1->compareTo($obj2, $C);
            return true;
        } catch (NotEqualException $ex) {
            return false;
        }
    }
}