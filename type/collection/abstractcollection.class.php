<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Type\Collection;

use Traversable;

abstract class AbstractCollection implements ICollection {

    private $mList = array();

    final function __construct() {

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->mList);
    }

    /**
     * Return the number of filtered items or the number of items in the collection if no filters are applied
     * @return int
     */
    function count() {
        return count($this->mList);
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

}
