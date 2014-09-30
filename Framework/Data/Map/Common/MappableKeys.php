<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 1:00 AM
 */
namespace CPath\Framework\Data\Map\Common;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IMappableKeys;

class MappableKeys implements IMappableKeys
{
    private $mValues = array();

    public function add($key, $value) {
        $this->mValues[$key] = $value;
    }


    /**
     * Map data to a data map
     * @param IKeyMap $Map the map instance to add data to
     * @return void
     */
    function mapKeys(IKeyMap $Map)
    {
        foreach ($this->mValues as $key => $value)
            $Map->map($key, $value);
    }
}