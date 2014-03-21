<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/19/14
 * Time: 11:18 PM
 */
namespace CPath\Framework\Route\Map\Common;

use CPath\Framework\Render\IRender;
use CPath\Framework\Route\Map\IRouteMap;
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
     * @param IRender $Destination
     * @return void
     */
    function mapRoute($prefix, IRender $Destination)
    {
        list($method, $path) = explode(' ', $prefix, 2);

        if(!$path)
            $path = '/' . str_replace('\\', '/', strtolower(get_class($this->mTarget)));
        elseif($path[0] !== '/')
            $path = '/' . str_replace('\\', '/', strtolower(dirname(get_class($this->mTarget)))) . '/' . $path;

        $callback = $this->mCallback;
        $callback($method . ' ' . $path, $Destination);
    }
}