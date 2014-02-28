<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 6/25/13
 * Time: 12:13 AM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\Data\Collection\Predicate;

interface IPredicate {

    /**
     * Filter object by true or false
     * @param mixed $Object
     * @return bool
     */
    function onPredicate($Object);
}