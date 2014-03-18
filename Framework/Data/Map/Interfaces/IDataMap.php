<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Map\Interfaces;

interface IDataMap {

    /**
     * Map data to a key in the map
     * @param String $key
     * @param mixed|Callable $data
     * @param int $flags
     * @return void
     */
    function mapDataToKey($key, $data, $flags=0);
}
