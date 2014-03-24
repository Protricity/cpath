<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Route\Map;

use CPath\Framework\Render\IRenderAggregate;
use CPath\Route\IRoute;

interface IRouteMap {

    /**
     * Map data to a key in the map
     * @param String $prefix
     * @param \CPath\Framework\Render\IRenderAggregate $Destination
     * @return bool if true the mapping will discontinue
     */
    function mapRoute($prefix, IRenderAggregate $Destination);
}

