<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Collection\Model;

use CPath\Framework\Data\Collection\ICollectionItem;
use CPath\Framework\Data\Collection\Predicate\IPredicate;

class CallbackFilter implements IPredicate {

    private $mCallable;

    public function __construct($callable) {
        $this->mCallable = $callable;
    }


    /**
     * @param ICollectionItem $Item
     * @return bool
     */
    function filterItem(ICollectionItem $Item) {
        $call = $this->mCallable;
        return $call($Item) ? true : false;
    }

    /**
     * Filter object by true or false
     * @param mixed $Object
     * @return bool
     */
    function onPredicate($Object) {
        $call = $this->mCallable;
        return $call($Object) ? true : false;
    }
}