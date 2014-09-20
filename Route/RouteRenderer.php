<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/8/14
 * Time: 12:34 AM
 */
namespace CPath\Route;

use CPath\Framework\Response\Types\ExceptionResponse;
use CPath\Request\IRequest;
use CPath\Request\IStaticRequestHandler;
use CPath\Request\Common\ExceptionRequest;

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
     * @param null $_arg Additional varargs will be sent to the Request Handler
     * @return bool if true the rendering has occurred
     */
    function route($prefix, $target, $_arg=null) {

        if($this->mRequest->match($prefix)) {
            if($target instanceof IRoutable) {
                return $target->mapRoutes($this);
            }

            if (is_callable($target)) {
                $target($this->mRequest);
                return true;
            }

            $args = array($this->mRequest);
            for($i=2; $i<func_num_args(); $i++)
                $args[] = func_get_arg($i);

            try{
                call_user_func_array(array($target, 'handleStaticRequest'), $args);

            } catch (\Exception $ex) {
                $this->mRequest = new ExceptionRequest($ex, $this->mRequest);
                return false;

            }
            return true;
        }
        return false;
    }
}