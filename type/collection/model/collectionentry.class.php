<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Type\Collection\Model;

use CPath\Type\Collection\ICollectionItem;

class CollectionEntry {
    private $mItem;

    private $mFiltered = false;

    function __construct(ICollectionItem $Item) {
        $this->mItem = $Item;
    }

    /** @param boolean $filtered */
    public function setFiltered($filtered) {
        $this->mFiltered = $filtered ? true : false;
    }

    /**
     * Is Item filtered?
     * @return boolean
     */
    public function isFiltered() {
        return $this->mFiltered;
    }

    /**
     * @return ICollectionItem
     */
    public function getItem() {
        return $this->mItem;
    }
}