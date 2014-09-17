<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/8/14
 * Time: 12:34 AM
 */
namespace CPath\Route;

use CPath\Request\Executable\IPrompt;
use CPath\Request\IRequest;
use CPath\Request\IStaticRequestHandler;

final class RouteRenderer implements IRouteMap
{
    private $mRequest;

    /**
     * Create a rendering map for IRoutable route maps
     * @param IRequest $Request the request instance to render
     */
    public function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }


    /**
     * Maps a route prefix to a target class or instance, and performs a render
     * @param String $prefix route prefix i.e. GET /my/path
     * @param String|IStaticRequestHandler $target the route target or instance
     * @throws \Exception
     * @return bool if true the rendering has occurred
     */
    function route($prefix, $target) {
        list($method, $path) = explode(' ', $prefix, 2);
        if ($method === 'ANY' || $method == $this->mRequest->getMethodName()) {
            if (strpos($this->mRequest->getPath(), $path) === 0) {

                if($target instanceof IRoutable) {
                    return $target->mapRoutes($this);
                }

                if($this->mRequest instanceof IPrompt) {
                    foreach(explode('/', trim($path, '/')) as $pathArg) {
                        // Consume arg
                        $arg = $this->mRequest->prompt("Internal error consuming request path after match");
                        if($arg !== $pathArg)
                            throw new \Exception("Internal error consuming request. '$arg' != '$pathArg'");
                    }
                }

                if (is_callable($target)) {
                    $target($this->mRequest);
                    return true;
                }

                $target::handleStaticRequest($this->mRequest);
                return true;

                //throw new \Exception("Class '" . get_class($target) . "' does not implement IRequestHandler");
            }
        }

        return false;
    }
}