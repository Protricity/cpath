<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Collection;

use CPath\Framework\Data\Collection\Predicate\IPredicate;
use Traversable;

abstract class AbstractCollection implements ICollection {

    private $mList = array();

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
        return new \ArrayIterator($this->mList);
    }
    /**
     * Add an item to the collection
     * @param ICollectionItem $Item
     * @return AbstractCollection return self
     */
    protected function addItem(ICollectionItem $Item) {
        $this->mList[] = $Item;
        return $this;
    }

    /**
     * Return an array of items
     * @return ICollectionItem[]
     */
    public function getItems() {
        return $this->mList;
    }

    /**
     * Filter the item collection by an IPredicate
     * @param IPredicate $Where
     * @return ICollection
     */
    function where(IPredicate $Where) {
        $list = array();
        foreach($this->mList as $Item) {
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
            return count($this->mList);
        $Results = $this->where($Where);
        return $Results->count();
    }


    /**
     * Filter the item collection by an IPredicate
     * @param IPredicate $Where
     * @return ICollection
     */
    function contains(IPredicate $Where) {
        return $this->count($Where) > 0;
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

}
