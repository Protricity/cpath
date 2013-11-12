<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IComparable {

    /**
     * Compare two objects
     * @param IComparable $obj the object to compare against $this
     * @param IComparator $C the IComparator instance
     * @return integer < 0 if $this is less than $obj; > 0 if $this is greater than $obj, and 0 if they are equal.
     */
    function compareTo(IComparable $obj, IComparator $C);
}