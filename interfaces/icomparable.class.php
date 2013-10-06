<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

use CPath\Handlers\IAPIField;
use CPath\Handlers\IAPIValidation;
use CPath\Handlers\ValidationExceptions;


interface IComparable {

    /**
     * Compare two objects
     * @param IComparable $obj the object to compare against $this
     * @param IComparator $Comparator the IComparator instance
     * @return integer < 0 if $this is less than $obj; > 0 if $this is greater than $obj, and 0 if they are equal.
     */
    function compareTo(IComparable $obj, IComparator $Comparator);
}