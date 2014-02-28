<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Collection\Predicate\Common;

use CPath\Framework\Data\Collection\Predicate\IPredicate;

class ClassPredicate implements IPredicate {

    private $mClassName;

    function __construct($class) {
        $this->mClassName = $class;
    }

    /**
     * Filter object by true or false
     * @param Object $Object
     * @return bool
     */
    function onPredicate($Object) {
        return get_class($Object) === $this->mClassName;
    }
}