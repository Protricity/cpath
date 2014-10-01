<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/17/14
 * Time: 7:21 PM
 */
namespace CPath\Route;

use CPath\Data\Map\IMappableKeys;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\IMappableSequence;
use CPath\Response\IResponse;
use CPath\Response\IResponseCode;

class RouteIndexResponse implements IResponse, ISequenceMap
{
    private $mRoutes;
    private $mMatch;

    public function __construct(IRoutable $Routes, $matchPrefix = 'ANY /') {
        $this->mRoutes = $Routes;
        $this->mMatch = $matchPrefix;
    }

    /**
     * Get the IResponse Message
     * @return String
     */
    function getMessage() {
        return 'Route Index: ' . $this->mMatch;
    }

    /**
     * Get the request status code
     * @return int
     */
    function getCode() {
        return IResponseCode::STATUS_SUCCESS;
    }

    /**
     * Map sequential data to the map
     * @param IMappableSequence $Map
     * @internal param \CPath\Route\IRequest $Request
     * @return mixed
     */
    function mapSequence(IMappableSequence $Map) {
        $match = $this->mMatch;
        $this->mRoutes->mapRoutes(new RouteCallback(function ($prefix, $target) use ($Map, $match) {
            list($matchMethod, $matchPath) = explode(' ', $match, 2);
            list($routeMethod, $routePath) = explode(' ', $prefix, 2);

            if ($routeMethod !== 'ANY' && $matchMethod !== 'ANY' && $routeMethod == $matchMethod)
                return false;

            if(strpos($routePath, $matchPath) !== 0)
                return false;

            return $Map->mapNext(new RouteLink($prefix, $target));
        }));
    }
}

