<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Collection;

use CPath\Framework\Data\Collection\Predicate\IPredicate;
use CPath\Framework\Data\Compare\IComparable;
use CPath\Framework\Data\Misc\ICloneable;
use Traversable;

abstract class AbstractCollection implements ICollection {

    private $mItems = array();

    final function __construct() { // For new static()

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|ICollectionItem An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->mItems);
    }
    /**
     * Add an item to the collection
     * @param ICollectionItem $Item
     * @return AbstractCollection return self
     */
    protected function addItem(ICollectionItem $Item) {
        $this->mItems[] = $Item;
        return $this;
    }


    /**
     * Remove an entry from the collection
     * @param ICollectionItem $Item
     * @return void
     */
    protected function removeItem(ICollectionItem $Item) {
        foreach($this->mItems as $i => $Item2) {
            if($Item === $Item2) {
                unset($this->mItems[$i]);
                return;
            }
        }
    }

    /**
     * Return an array of items
     * @return ICollectionItem[]
     */
    public function getItems() {
        return $this->mItems;
    }

    /**
     * Filter the item collection by an IPredicate
     * @param IPredicate $Where
     * @return ICollection
     */
    function where(IPredicate $Where) {
        $list = array();
        foreach($this->mItems as $Item) {
            if($Where->onPredicate($Item) === true)
                $list[] = $Item;
        }

        /** @var AbstractCollection $Inst */
        $Inst = new static(); // TODO: hack? Only if constructor not final
        foreach($list as $Item)
            $Inst->addItem($Item);

        return $Inst;
    }

    /**
     * Return the number of filtered items or the number of items in the collection if no filters are applied
     * @param IPredicate $Where
     * @return int
     */
    function count(IPredicate $Where=null) {
        if($Where === null)
            return count($this->mItems);
        $Results = $this->where($Where);
        return $Results->count();
    }


    /**
     * @param Callable $callable
     * @return mixed|array
     */
    function each($callable) {
        $return = array();
        foreach($this->getItems() as $Item) {
            $return[] = $callable($Item);
        }
        return $return;
    }

    /**
     * Checks for the existence of a item in the collection
     * @param ICollectionItem $Item
     * @return bool
     */
    function contains(ICollectionItem $Item)
    {
        foreach($this->getItems() as $Item2)
            if($Item === $Item2)
                return true;

        return false;
    }

    /**
     * Implement ICloneable
     */
    function __clone()
    {
        //$this->mItems = clone $this->mItems;
    }
}
