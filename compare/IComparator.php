<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/31/13
 * Time: 10:27 AM
 */
namespace CPath\Compare;

class NotEqualException extends \Exception {

    private $mC = NULL;

    /**
     * @param string $message the reason for the inequality
     * @param integer $c < 0 if $this is less than $obj; > 0 if $this is greater than $obj, and 0 if they are equal.
     * @param \Exception|NULL $previous
     */
    function __construct($message, $c=0, \Exception $previous=NULL) {
        $this->mC = (int)$c;
        parent::__construct($message, $c, $previous);
    }

    /**
     * Return the difference (usually 1 or -1
     * @return integer < 0 if $obj1 is less than $obj2; > 0 if $obj1 is greater than $obj2, and 0 if they are equal.
     */
    function getDiff() {
        return $this->mC;
    }
}


interface IComparator
{

    /**
     * Compare two variables of any type
     * @param mixed $o1
     * @param mixed $o2
     * @param String|NULL $name the name of the data being compared
     * @return void
     * @throws NotEqualException if the objects were not equal
     */
    function compare($o1, $o2, $name = NULL);

    /**
     * Compare two objects
     * @param IComparable $o1
     * @param IComparable $o2
     * @throws NotEqualException if the objects were not equal
     * @return void
     */
    function compareObj(IComparable $o1, IComparable $o2);

    /**
     * Compare two arrays
     * @param Array $a1
     * @param Array $a2
     * @param String|NULL $_ignoreKeys varargs of keys to ignore
     * @throws NotEqualException if the objects were not equal
     * @return void
     */
    function compareArray(Array $a1, Array $a2, $_ignoreKeys = NULL);

    /**
     * Compare two scalars
     * @param mixed $s1
     * @param mixed $s2
     * @param String|NULL $name the name of the data
     * @throws NotEqualException if the objects were not equal
     * @return void
     */
    function compareScalar($s1, $s2, $name = null);

}