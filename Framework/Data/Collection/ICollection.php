<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 6/25/13
 * Time: 12:13 AM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\Data\Collection;

use CPath\Framework\Data\Collection\Predicate\IPredicate;
use CPath\Framework\Data\Misc\ICloneable;

interface ICollection extends \IteratorAggregate, \Countable, ICloneable {

    /**
     * Return an array of items
     * @return ICollectionItem[]
     */
    function getItems();

    /**
     * Filter the item collection by an IPredicate
     * @param IPredicate $Where
     * @return ICollection
     */
    function where(IPredicate $Where);

    /**
     * Checks for the existence of a item in the collection
     * @param ICollectionItem $Item
     * @return bool
     */
    function contains(ICollectionItem $Item);

    /**
     * @param Callable $callable
     * @return mixed|array
     */
    function each($callable);
}