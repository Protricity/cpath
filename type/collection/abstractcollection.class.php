<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Type\Collection;

use CPath\Type\Collection\Model\CollectionEntry;
use Traversable;

abstract class AbstractCollection implements ICollection {

    /** @var CollectionEntry[] */
    private $mList = array();
    private $mFiltered = false;

    function __construct() {

    }

    /**
     * Add an item to the collection
     * @param ICollectionItem $Item
     * @return AbstractCollection return self
     */
    protected function addItem(ICollectionItem $Item) {
        $this->mList[] = new CollectionEntry($Item);
        return $this;
    }

    /**
     * Filter the item list by class name
     * @param String $className - class name to filter by
     * @return AbstractCollection return self
     */
    function whereClass($className) {
        return $this->where(function($Item) use ($className) {
            return $className === get_class($Item);
        });
    }

    /**
     * Filter the item list by a callback
     * @callback bool function($Item)
     * @param Callable|\Closure $callback - callback to filter by. Return === true to keep a item in the collection
     * @return AbstractCollection return self
     */
    function where($callback) {
        foreach($this->mList as $Item) {
            if($Item->isFiltered())
                continue;

            if(true !== $callback($Item->getItem()))
                $Item->setFiltered(true);
        }

        $this->mFiltered = true;
        return $this;
    }

    /**
     * Return a list of items as filtered or all items if no filters are applied
     * @return Array
     */
    function getFiltered() {
        if(!$this->mFiltered)
            return $this->getAll();

        $list = array();
        foreach($this->mList as $Item) {
            if($Item->isFiltered())
                continue;

            $list[] = $Item->getItem();
        }

        return $list;
    }

    /**
     * Return a list of all items in the collection
     * @return Array
     */
    function getAll() {
        $list = array();
        foreach($this->mList as $Item)
            $list[] = $Item->getItem();

        return $list;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->getFiltered());
    }

    /**
     * Return the number of filtered items or the number of items in the collection if no filters are applied
     * @return int
     */
    function count() {
        return count($this->getFiltered());
    }

    /**
     * Reset all filters
     * @return AbstractCollection return self
     */
    function reset() {
        foreach($this->mList as $Item)
            $Item->setFiltered(false);
        $this->mFiltered = false;
    }

    /**
     * Permanently remove all items as filtered from the collection
     * @return AbstractCollection return self
     */
    function removeFiltered()
    {
        $list = array();
        foreach($this->mList as $Item)
            if(!$Item->isFiltered())
                $list[] = $Item;

        $this->mList = $list;
        $this->mFiltered = false;
    }
}
