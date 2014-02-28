<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Collection\Predicate\Common;

use CPath\Framework\Data\Collection\Predicate\IPredicate;

class CallbackPredicate implements IPredicate {

    private $mCallback;

    function __construct($callable) {
        $this->mCallback = $callable;
    }

    /**
     * Filter object by true or false
     * @param Object $Object
     * @return bool
     */
    function onPredicate($Object) {
        $call = $this->mCallback;
        return $call($Object) === true;
    }
}