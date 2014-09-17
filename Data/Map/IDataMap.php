<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Data\Map;

interface IDataMap {
    /**
     * Map a sequential value to this map
     * @param String $value
     * @return void
     */
    function mapValue($value);

    /**
     * Map data to a key in the map
     * @param String $name
     * @param mixed $value
     * @return void
     */
    function mapNamedValue($name, $value);
}
