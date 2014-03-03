<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\Data\Compare\Util;

use CPath\Framework\Data\Compare\IComparable;

class CompareUtil {

    /**
     * Compare two variables of any type
     * @param mixed $o1
     * @param mixed $o2
     * @return integer < 0 if $o1 is less than $o2; > 0 if $o1 is greater than $o2, and 0 if they are equal.
     */
    function compare($o1, $o2) {
        if(is_scalar($o1)) {
            if(!is_scalar($o2))
                return 1;
            return $this->compareScalar($o1, $o2);
        } elseif (is_array($o1)) {
            if(!is_array($o2))
                return 1;
            return $this->compareArray($o1, $o2);
        } else {
            if(($n1 = get_class($o1)) != ($n2 = get_class($o2)))
                return strcmp($n1, $n2);
            
            if($o1 instanceof IComparable) {
                if(!$o2 instanceof IComparable)
                    return 1;
            } else {
                if($o2 instanceof IComparable)
                    return -1;
                return $this->compareObj($o1, $o2);
            }
            
            return $o1->compareTo($o2);
        }
    }

    /**
     * Compare two arrays
     * @param Array $a1
     * @param Array $a2
     * @return integer < 0 if $a1 is less than $a2; > 0 if $a1 is greater than $a2, and 0 if they are equal.
     */
    function compareArray(Array $a1, Array $a2) {
        $s1 = sizeof($a1);
        $s2 = sizeof($a2);

        if($s1 != $s2)
            return $s1 - $s2;

        foreach($a1 as $k1=>$v1) {
            if(!array_key_exists($k1, $a2))
                return 1;
            $v2 = $a2[$k1];
            $c = $this->compare($v1, $v2);
            if($c)
                return $c;
        }

        return 0;
    }

    /**
     * Compare two scalars
     * @param mixed $s1
     * @param mixed $s2
     * @throws \InvalidArgumentException
     * @return integer < 0 if $s1 is less than $s2; > 0 if $s1 is greater than $s2, and 0 if they are equal.
     */
    function compareScalar($s1, $s2) {

        if(is_string($s1)) {
            if(!is_string($s2))
                return 1;
            return strcmp($s1, $s2);
        }

        if(is_numeric($s1)) {
            if(!is_numeric($s2))
                return 1;
            return $s1 - $s2;
        }

        throw new \InvalidArgumentException("Invalid scalars");
    }

    /**
     * Compare two objects
     * @param IComparable $o1
     * @param IComparable $o2
     * @return integer < 0 if $o1 is less than $o2; > 0 if $o1 is greater than $o2, and 0 if they are equal.
     */
    function compareObj($o1, $o2) {
        $c1 = get_class($o1);
        $c2 = get_class($o2);
        if($c2 !== $c1)
            return strcmp($c1, $c2);

        return strcmp((String)$o1, (String)$o2);
    }

}