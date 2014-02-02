<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Type\Collection\Predicates;

use CPath\Type\Collection\IPredicate;

class InversePredicate implements IPredicate {

    private $mPredicate;

    function __construct(IPredicate $Predicate) {
        $this->mPredicate = $Predicate;
    }

    /**
     * Filter object by true or false
     * @param Object $Object
     * @return bool
     */
    function onPredicate($Object) {
        return !$this->mPredicate->onPredicate($Object);
    }
}