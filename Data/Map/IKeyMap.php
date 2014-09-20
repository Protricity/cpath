<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Data\Map;

interface IKeyMap {

    /**
     * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String $key
     * @param String|Array|IMappableKeys|IMappableSequence $value
     * @return bool true to stop or any other value to continue
     */
    function map($key, $value);
}

