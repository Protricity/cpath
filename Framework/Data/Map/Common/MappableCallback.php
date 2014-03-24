<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Map\Common;

use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;

class MappableCallback implements IMappable {
    const IS_FIRST = 0x01;

    private $mCallback, $mCount=0;

    function __construct($callback) {
        $this->mCallback = $callback;
    }
//
//    /**
//     * Map data to a key in the map
//     * @param String $key
//     * @param mixed $value
//     * @param int $flags
//     * @return void
//     */
//    function mapKeyValue($key, $value, $flags = 0)
//    {
//        $flags = 0;
//        if($this->mCount == 0)
//            $flags |= static::IS_FIRST;
//        $call = $this->mCallback;
//        $call($key, $value, $flags);
//        $this->mCount++;
//    }

    /**
     * Map data to a data map
     * @param IDataMap $Map the map instance to add data to
     * @return void
     */
    function mapData(IDataMap $Map)
    {
        $call = $this->mCallback;
        $call($Map);
    }
}