<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Collection\Predicate\Common;

class InversePredicate implements \CPath\Framework\Data\Collection\Predicate\IPredicate {

    private $mPredicate;

    function __construct(\CPath\Framework\Data\Collection\Predicate\IPredicate $Predicate) {
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