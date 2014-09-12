<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/8/14
 * Time: 12:34 AM
 */
namespace CPath\Route;

use CPath\Request\IRequestHandler;

final class RequestRouteMatcher implements IRouteMap
{

    private $mMethod;
    private $mPath;
    private $mTarget = null;

    public function __construct($method, $path) {
        $this->mMethod = $method;
        $this->mPath = $path;
    }

    function targetFound() {
        return $this->mTarget !== null;
    }

    /**
     * @return String|Callable|IRequestHandler
     * @throws \InvalidArgumentException
     */
    function getTarget() {
        if ($this->targetFound())
            throw new \InvalidArgumentException("Target was not found");
        return $this->mTarget;
    }

    /**
     * Map a Route prefix to a target class or instance
     * @param String $prefix route prefix i.e. GET /my/path
     * @param String|mixed $target the route target or instance
     * @return bool if true the mapping will discontinue
     */
    function route($prefix, $target) {
        if ($this->targetFound())
            return true;
        list($method, $path) = explode(' ', $prefix, 2);
        if ($method === 'ANY' || $method == $this->mMethod) {
            if (strpos($this->mPath, $path) === 0) {
//                $argPath = substr($requestPath, strlen($path));
//                $argPath = trim($argPath, '/');
//                $args = explode('/', $argPath);
//                $this->mArgs = $args;


//
//                if($target instanceof Routes_LazyRender)
//                    $Destination = $Destination->getInstance();
//                $this->mDestination = $Destination;
                //$Destination->render($this->mRequest);

                //$prefix2 = $method . ' ' . $requestPath;
                //Log::u(__CLASS__, "Matched [{$prefix2}] to [{$prefix}]");
                $this->mTarget = $target;
                return true;
            }
        }

        //$prefix2 = $this->mRequest->getMethodName() . ' ' . $this->mRequest->getPath();
        //Log::v2(__CLASS__, "Could NOT Match [{$prefix2}] to [{$prefix}]");
        return false;
    }
}