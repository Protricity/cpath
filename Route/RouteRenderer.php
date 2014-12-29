<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/8/14
 * Time: 12:34 AM
 */
namespace CPath\Route;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;


final class RouteRenderer implements IRouteMapper
{
    //const INTERFACE_CALLBACKS = 'CPath\\Route\\IRouteRendererStaticCallbacks';
    const INTERFACE_ROUTE = 'CPath\\Route\\IRoute';
    const REQUEST_METHOD = 'routeRequestStatic';

    private $mRequest;
    //private $mUnhandled = array();
    /** @var IRoutable[] */
    private $mHandlers = array();
    private $mPrevious = array();

    /**
     * Create a rendering map for IRoutable route maps
     * @param IRequest $Request the request inst to render
     */
    public function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

    function renderRoutes(IRouteMap $Map) {
	    $this->mPrevious = array();
        if($Map->mapRoutes($this))
            return true;

	    $c = sizeof($this->mPrevious);
	    if($c > 0) {
		    if($this->mPrevious[0] instanceof \Exception) {
			    $ex = $this->mPrevious[0];

			} else {
			    $cls = array();
			    foreach($this->mPrevious as $Previous)
				    $cls[] = get_class($Previous);
				$ex = new RequestException("Unhandled class: " . implode(', ', array_unique($cls)));
		    }

	    } else {
			$routePrefix = 'GET ' . $this->mRequest->getPath();
		    $this->mPrevious[] = new RouteIndex($Map, $routePrefix);

		    if($Map->mapRoutes($this))
			    return true;

		    $ex = new RequestException("Route not found: " . $this->mRequest->getPath());
	    }

	    if($this->tryHandlers($this->mRequest, $this->mHandlers, array_merge(array($ex), $this->mPrevious)))
		    return true;

	    throw $ex;
    }

    /**
     * Maps a route prefix to a target class or inst, and performs a render
     * @param String $prefix route prefix i.e. GET /my/path
     * @param String|IRoutable $target the route target or inst
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
            $args = array($this->mRequest, &$this->mPrevious);
            for($i=2; $i<func_num_args(); $i++)
                $args[] = func_get_arg($i);
            $Response = call_user_func_array(array($target, self::REQUEST_METHOD), $args);
            if($Response === null || $Response === true) {
                return true;
            }

            if($Response === false) {
	            array_unshift($this->mHandlers, $target);
	            if (sizeof($this->mPrevious) >= 1 && $this->tryHandlers($this->mRequest,
		            array_diff($this->mHandlers, array($target)),
		            $this->mPrevious))
		            return true;
	            return false;
            }

	        if ($this->tryHandlers($this->mRequest,
		        array_diff($this->mHandlers, array($target)),
		        array_merge(array($Response), $this->mPrevious)))
		        return true;

	        array_unshift($this->mPrevious, $Response);


        } catch (\Exception $ex) {

	        if ($this->tryHandlers($this->mRequest,
		        array_diff($this->mHandlers, array($target)),
		        array_merge(array($ex), $this->mPrevious)))
		        return true;

	        array_unshift($this->mPrevious, $ex);
        }

        return false;
    }

	protected function tryHandlers(IRequest $Request, Array $Handlers, Array $Previous) {

		foreach($Handlers as $i => $Handler) {
			/** @var IRoutable $Handler */
			$Response = $Handler::routeRequestStatic($Request, $Previous);
			if($Response === false)
				continue;

			if($Response === true || !$Response) {
				return true;
			}

			array_unshift($Previous, $Response);
			return $this->tryHandlers($Request, array_diff($this->mHandlers, array($Handler)), $Previous);
		}

		return false;
	}
}