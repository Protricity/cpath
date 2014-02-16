<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Map\Types;

use CPath\Framework\Data\Map\Interfaces\IDataMap;

class CallbackMap implements IDataMap {
    const IS_FIRST = 0x01;

    private $mCallback, $mCount=0;

    function __construct($callback) {
        $this->mCallback = $callback;
    }

    /**
     * Map data to a key in the map
     * @param String $key
     * @param mixed $data
     * @param int $flags
     * @return void
     */
    function mapDataToKey($key, $data, $flags = 0)
    {
        $flags = 0;
        if($this->mCount == 0)
            $flags |= static::IS_FIRST;
        $call = $this->mCallback;
        $call($key, $data, $flags);
    }

}