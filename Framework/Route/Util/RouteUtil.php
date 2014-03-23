<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/22/14
 * Time: 4:34 PM
 */
namespace CPath\Framework\Route\Util;

use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Route\Exceptions\RouteNotFoundException;
use CPath\Framework\Route\Map\Common\CallbackRouteMap;
use CPath\Framework\Route\Render\IDestination;
use CPath\Framework\Route\Routable\IRoutable;

class RouteUtil {
    private $mRoutable;

    public function __construct(IRoutable $Routable) {
        $this->mRoutable = $Routable;
    }

    public function renderDestination(IRequest $Request, $path, $args) {
        $Target = $this->mRoutable;
        $FoundDestination = null;
        // TODO: retreat from using IRoutable
        $Target->mapRoutes(new CallbackRouteMap($Target, function($prefix, IDestination $Destination) use ($Request, &$FoundDestination, &$First) {
            if(!$First)
                $First = $Destination;
            list($method, $path) = explode(' ', $prefix, 2);
            if($Request->getMethod() === $method || $method === 'ANY') {
                if(strpos($Request->getPath(), $path) === 0) {
                    $FoundDestination = $Destination;
                    return true;
                }
            }
            return false;
        }));
        if(!$FoundDestination)
            throw new RouteNotFoundException("Route not found: " . $path);

        if(!$FoundDestination instanceof IDestination)
            throw new \InvalidArgumentException("Class does not implement IDestination: " . get_class($Target));

        $FoundDestination->renderDestination($Request, $path, $args);
    }
}

