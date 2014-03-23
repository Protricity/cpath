<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/19/14
 * Time: 11:18 PM
 */
namespace CPath\Framework\Route\Map\Common;

use CPath\Framework\Route\Map\IRouteMap;
use CPath\Framework\Route\Render\IDestination;
use CPath\Framework\Route\Routable\IRoutable;

class CallbackRouteMap implements IRouteMap
{
    private $mCallback, $mTarget;

    public function __construct(IRoutable $Target, $callback)
    {
        $this->mTarget = $Target;
        $this->mCallback = $callback;
    }

    /**
     * Map data to a key in the map
     * @param String $prefix
     * @param IDestination $Destination
     * @return void
     */
    function mapRoute($prefix, IDestination $Destination)
    {
        if(strpos($prefix, ' ') !== false) {
            list($method, $path) = explode(' ', $prefix, 2);
            if($path[0] !== '/')
                $path = '/' . str_replace('\\', '/', strtolower(dirname(get_class($this->mTarget)))) . '/' . $path;
        } else {
            $method = $prefix;
            $path = '/' . str_replace('\\', '/', strtolower(get_class($this->mTarget)));
        }

        $callback = $this->mCallback;
        $callback($method . ' ' . $path, $Destination);
    }
}