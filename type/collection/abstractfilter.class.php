<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 6/25/13
 * Time: 12:13 AM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Type\Collection;

abstract AbstractFilter implements ICollectionFilter {

    /**
     * @param ICollectionItem $Item
     * @return bool
     */
    function filterItem(ICollectionItem $Item) {

    }
}