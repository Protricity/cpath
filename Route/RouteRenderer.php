<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/8/14
 * Time: 12:34 AM
 */
namespace CPath\Route;

use CPath\Render\IRenderAll;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;

final class RouteRenderer implements IRouteMapper, IRouteMap
{
    //const INTERFACE_CALLBACKS = 'CPath\\Route\\IRouteRendererStaticCallbacks';
    const INTERFACE_ROUTE = 'CPath\\Route\\IRoute';
    const REQUEST_METHOD = 'routeRequestStatic';

    private $mRequest;
    //private $mUnhandled = array();
    /** @var IRoutable[] */
    private $mHandlers = array();
    private $mPrevious = array();

	/** @var IRouteMap[] */
	private $mActiveMaps = array();

    /**
     * Create a rendering map for IRoutable route maps
     * @param IRequest $Request the request inst to render
     */
    public function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

	/**
	 * @return IRouteMap
	 */
	public function getActiveMap() {
		return $this->mActiveMaps;
	}

    function renderRoutes(IRouteMap $Map) {
	    $this->mActiveMaps[] = $Map;
	    $this->mPrevious = array();
        if($Map->mapRoutes($this))
	        return true;

	    $c = sizeof($this->mPrevious);
	    if($c > 0) {
		    $Object = $this->mPrevious[0];
		    if($Object instanceof IRenderAll) {
			    $Object->render($this->mRequest, true);
			    return true;
		    }

		    if($Object instanceof \Exception) {
			    $ex = $Object;

			} else {
			    $cls = array();
			    foreach($this->mPrevious as $Previous)
				    $cls[] = get_class($Previous);
				$ex = new RequestException("Unhandled class: " . implode(', ', array_unique($cls)));
		    }

	    } else {
		    $ex = new RouteNotFoundException("Route not found: " . $this->mRequest->getPath());
	    }

	    if($this->tryHandlers($this->mRequest, $this->mHandlers, array_merge(array($ex), $this->mPrevious)))
		    return true;

	    throw $ex;
    }

    /**
     * Maps a route prefix to a target class or inst, and performs a render
     * @param String $prefix route prefix i.e. GET /my/path
     * @param String|IRoutable $target the route target or inst
     * @param null $arg Additional varargs will be sent to the Request Handler
     * @return bool if true the request was handled and should end
     */
    function route($prefix, $target, $arg=null) {

        if(!$this->mRequest->match($prefix, is_int($arg) ? $arg : 0))
            return false;

        if($target instanceof IRouteMap) {
            return $target->mapRoutes($this);
        }

        $args = array($this->mRequest, &$this->mPrevious, $this, array());
        for($i=2; $i<func_num_args(); $i++)
            $args[3][] = func_get_arg($i);

	    try {
		    $i = class_implements($target);
		    if(in_array(IRoutable::INTERFACE_CLASS, $i)) {
			    $Response = call_user_func_array(array($target, self::REQUEST_METHOD), $args);
		    } else {
			    return false;
		    }

	    } catch (IResponse $ex) {
		    $Response = $ex;

	    } catch (\Exception $ex) {
		    $Response = new ExceptionResponse($ex);
	    }

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
	    $Previous = $this->mPrevious;
	    if(!in_array($Response, $Previous, true))
		    array_unshift($Previous, $Response);

        if ($this->tryHandlers($this->mRequest,
	        array_diff($this->mHandlers, array($target)),
	        $Previous))
	        return true;

		$this->mPrevious = $Previous;
        return false;
    }

	protected function tryHandlers(IRequest $Request, Array $Handlers, Array $Previous) {

		foreach($Handlers as $i => $Handler) {
			/** @var IRoutable $Handler */
			$Response = $Handler::routeRequestStatic($Request, $Previous, $this, array());
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

	/**
	 * Maps all routes to the route map. Returns true if the route prefix was matched
	 * @param IRouteMapper $Mapper
	 * @return bool true if the route mapper should stop mapping, otherwise false to continue
	 * @build routes --disable 0
	 * Note: Set --disable 1 or remove doc tag to stop code auto-generation on build for this method
	 */
	function mapRoutes(IRouteMapper $Mapper) {
		foreach($this->mActiveMaps as $Map)
			if($Map->mapRoutes($Mapper))
				return true;
		return false;
	}
}