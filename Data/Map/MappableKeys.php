<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 1:00 AM
 */
namespace CPath\Data\Map;

use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IKeyMap;

class MappableKeys implements IKeyMap
{
    private $mValues = array();

    public function add($key, $value) {
        $this->mValues[$key] = $value;
    }


    /**
     * Map data to a data map
     * @param IMappableKeys $Map the map instance to add data to
     * @internal param \CPath\Framework\Data\Map\Common\IRequest $Request
     * @return void
     */
    function mapKeys(IMappableKeys $Map) {
        foreach ($this->mValues as $key => $value)
            if($Map->map($key, $value))
                break;
    }
}