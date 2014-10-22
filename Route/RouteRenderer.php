<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/8/14
 * Time: 12:34 AM
 */
namespace CPath\Route;

use CPath\Request\IRequest;
use CPath\Request\Exceptions\RequestException;

final class RouteRenderer implements IRouteMapper
{
    //const INTERFACE_CALLBACKS = 'CPath\\Route\\IRouteRendererStaticCallbacks';
    const INTERFACE_ROUTE = 'CPath\\Route\\IRoute';
    const REQUEST_METHOD = 'routeRequestStatic';

    private $mRequest;
    //private $mUnhandled = array();
    /** @var IRoute[] */
    private $mHandlers = array();
    private $mPrevious = array();

    /**
     * Create a rendering map for IRoutable route maps
     * @param IRequest $Request the request instance to render
     */
    public function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

    function renderRoutes(IRouteMap $Map, $withDefaults=true) {
	    $this->mPrevious = array();
        if($Map->mapRoutes($this))
            return true;
        if($withDefaults) {
            $Defaults = new DefaultMap();
            if($Defaults->mapRoutes($this))
                return true;
        }

//	    $c = sizeof($this->mHandlers);
//	    $skip = array();
//	    for($i=0; $i<$c; $i++) {
//		    if(in_array($i, $skip))
//			    continue;
//		    $Handler = $this->mHandlers[$i];
//		    $Response = $Handler::routeRequestStatic($this->mRequest, $this->mPrevious);
//		    if($Response === false)
//			    continue;
//		    if($Response === true || !$Response) {
//			    return true;
//		    }
//		    array_unshift($this->mPrevious, $Response);
//		    $skip[] = $i;
//		    $i=-1;
//	    }

	    $c = sizeof($this->mPrevious);
	    if($c > 0) {
		    if($this->mPrevious[0] instanceof \Exception) {
			    $ex = $this->mPrevious[0];

			} else {
			    $cls = array();
			    foreach($this->mPrevious as $Previous)
				    $cls[] = get_class($Previous);
				$ex = new RequestException("Unhandled class: " . implode(', ', $cls));
			    array_unshift($this->mPrevious, $ex);
		    }

	    } else {
		    $ex = new RequestException("Route not found: " . $this->mRequest->getPath());
		    array_unshift($this->mPrevious, $ex);

	    }

        throw $ex;

    }

    /**
     * Maps a route prefix to a target class or instance, and performs a render
     * @param String $prefix route prefix i.e. GET /my/path
     * @param String|IRoute $target the route target or instance
     * @param null $_arg Additional varargs will be sent to the Request Handler
     * @return bool if true the request was handled and should end
     */
    function route($prefix, $target, $_arg=null) {

        if(!$this->mRequest->match($prefix))
            return false;

        if($target instanceof IRouteMap) {
            return $target->mapRoutes($this);
        }

        try {
            $args = array($this->mRequest, $this->mPrevious);
            for($i=2; $i<func_num_args(); $i++)
                $args[] = func_get_arg($i);
            $Response = call_user_func_array(array($target, self::REQUEST_METHOD), $args);
            if($Response === null || $Response === true) {
//                if(!headers_sent())
//                    throw new RequestException("IRoute failed to render or return content: " . $target);
                return true;
            }

            if($Response === false) {
	            $this->mHandlers[] = $target;
                return false;
            }

	        array_unshift($this->mPrevious, $Response);

	        for($i=0; $i<sizeof($this->mHandlers); $i++) {
		        /** @var IRoute $Handler */
		        $Handler = $this->mHandlers[$i];
                $Response = $Handler::routeRequestStatic($this->mRequest, $this->mPrevious);
                if($Response === false)
                    continue;
                if($Response === true || !$Response) {
//                    if(!headers_sent())
//                        throw new RequestException("IRoute Handler failed to render or return content: " . $Handler);
                    return true;
                }
	            array_unshift($this->mPrevious, $Response);

		        unset($this->mHandlers[$i]);
		        $this->mHandlers = array_values($this->mHandlers);
		        $i=-1;
            }


        } catch (\Exception $ex) {
	        array_unshift($this->mPrevious, $ex);
        }

        return false;
    }
}