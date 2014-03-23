<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 8:44 AM
 */
namespace CPath\Framework\Data\Map\Collection\Interfaces;

use CPath\Framework\Data\Map\Interfaces\IMappable;

interface ICollectionMap
{

    /**
     * Map an object to this array
     * @param IMappable $Mappable
     * @return void
     */
    function mapArrayObject(IMappable $Mappable);

    /**
     * Add a value to the array
     * @param mixed $value
     * @return void
     */
    function mapArrayValue($value);
}
