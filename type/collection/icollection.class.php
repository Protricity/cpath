<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 6/25/13
 * Time: 12:13 AM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Type\Collection;

interface ICollection extends \IteratorAggregate, \Countable {

    /**
     * Return a list of items as filtered or all items if no filters are applied
     * @return ICollection[]
     */
    function getFiltered();

    /**
     * Return a list of all items in the collection
     * @return ICollection[]
     */
    function getAll();

    /**
     * Filter the item list by class name
     * @param String $className - class name to filter by
     * @return ICollection return self
     */
    function whereClass($className);

    /**
     * Filter the item list by a callback
     * @callback bool function(ICollectionItem $item)
     * @param Callable|\Closure $callback - callback to filter by. Return === true to keep a item in the collection
     * @return ICollection return self
     */
    function where(ICollectionFilter $Filter);

    function exists(ICollectionFilter $Filter);

    /**
     * Return the number of filtered items or the number of items in the collection if no filters are applied
     * @return int
     */
    function count();

    /**
     * Reset all filters
     * @return ICollection return self
     */
    function reset();

    /**
     * Permanently remove all filtered items from the collection
     * @return ICollection return self
     */
    function removeFiltered();
}