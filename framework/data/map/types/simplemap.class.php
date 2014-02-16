<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Map\Types;

use CPath\Framework\Data\Map\Interfaces\IDataMap;

class SimpleMap implements IDataMap {
    private $mMap = array();

    /**
     * Map data to a key in the map
     * @param String $key
     * @param mixed $data
     * @param int $flags
     * @return void
     */
    function mapDataToKey($key, $data, $flags = 0)
    {
        $this->mMap[$key] = $data;
    }

    /**
     * Returns an associative array of keys and data
     * @return Array associative array
     */
    function getMapData()
    {
        return $this->mMap;
    }
}