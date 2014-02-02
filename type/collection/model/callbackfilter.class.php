<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Type\Collection\Model;

use CPath\Type\Collection\ICollectionItem;
use CPath\Type\Collection\IPredicate;

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
}