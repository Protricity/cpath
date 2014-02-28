<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 6/25/13
 * Time: 12:13 AM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\Data\Collection\Util;

use CPath\Framework\Data\Collection\ICollection;
use CPath\Framework\Data\Collection\Predicate\IPredicate;

interface ICollectionUtil extends ICollection{

    /**
     * Return true if at least one item matches the predicate
     * @param IPredicate $Where
     * @return boolean
     */
    function any(IPredicate $Where);

    /**
     * Return true if all items match the predicate
     * @param IPredicate $Where
     * @return boolean
     */
    function all(IPredicate $Where);

    /**
     * Permanently remove all filtered items from the collection
     * @param IPredicate|null $Where optional filter
     * @return ICollection return self
     */
    function remove(IPredicate $Where);
}