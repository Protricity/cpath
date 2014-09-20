<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Data\Map;

interface IMappableKeys {

    /**
     * Map data to a data map
     * @param IKeyMap $Map the map instance to add data to
     * @return void
     */
    function mapKeys(IKeyMap $Map);
}
