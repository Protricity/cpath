<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Common\RequestWrapper;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Exceptions\CodedException;
use CPath\Framework\Response\Interfaces\IResponseCode;
use CPath\Framework\Route\Exceptions\RouteNotFoundException;
use CPath\Framework\Route\Map\IRouteMap;
use CPath\Framework\Render\IRenderAggregate;
use CPath\Framework\Route\Routable\IRoutable;

class Routes implements IRoutable {

    /** @var IRouteMap */
    private $mRoutes;

    public function __construct() {
    }

    /**
     * Returns the route for this IRender
     * @param IRouteMap $Map
     */
    function mapRoutes(IRouteMap $Map)
    {
        $this->mRoutes = $Map;
        $Map = $this;
        $path = Config::getGenPath().'routes.gen.php';
        include $path;
    }

    /**
     * @param String $prefix
     * @param String $destination
     * @return bool if true the mapping will discontinue
     */
    function map($prefix, $destination) {
        $Routes = $this->mRoutes;
        return $Routes->mapRoute($prefix, new Routes_LazyRender($destination));
    }

    /**
     * Return the handler as requested
     * @param IRequest $Request the IRequest instance for this render
     * @return \CPath\Framework\Render\IRenderAggregate found handler
     */
    function getHandlerFromRequest(IRequest $Request) {
        $Selector = new Routes_SelectorMap($Request);
        $this->mapRoutes($Selector);
        return $Selector->getDestination();
    }

    /**
     * Route request to an IRender destination
     * @param IRequest $Request Request is passed by reference and updated with a RequestWrapper
     * @param Framework\Route\Routable\IRoutable $Routable
     * @throws Framework\Response\Exceptions\CodedException
     * @throws Framework\Route\Exceptions\RouteNotFoundException
     * @return IRender
     */
    function routeRequest(IRequest &$Request, IRoutable $Routable) {
        $path = $Request->getPath();
        if(($ext = pathinfo($path, PATHINFO_EXTENSION))
            && in_array(strtolower($ext), array('js', 'css', 'png', 'gif', 'jpg', 'bmp', 'ico')))
            throw new CodedException("File request was passed to Script: ", IResponseCode::STATUS_NOT_FOUND);

        $Selector = new Routes_SelectorMap($Request);
        $Routable->mapRoutes($Selector);

        $newPath = '';
        $args = array();
        $Selector->getMatchedData($Destination, $newPath, $args);

        $Request = new RequestWrapper($Request, $newPath, $args);

        if ($Destination instanceof IRenderAggregate)
            $Destination = $Destination->getRenderer($Request);

        if($Destination instanceof IRender)
            return $Destination;

        throw new RouteNotFoundException("Route was not found: " . $path);
    }

    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @throws Framework\Response\Exceptions\CodedException
     * @throws \Exception
     * @return String|void always returns void
     */
    function render(IRequest $Request) {
        $this->routeRequest($Request, $this)
            ->render($Request);
    }
}

class Routes_LazyRender implements IRenderAggregate {
    private $mDestination;
    public function __construct($destination) {
        $this->mDestination = $destination;
    }

    function getInstance() {
        /** @var \CPath\Framework\Render\IRenderAggregate $Inst */
        $Inst = $this->mDestination;
        $Inst = new $Inst;
        return $Inst;
    }

    /**
     * Return an instance of IRender
     * @param IRequest $Request the IRequest instance for this render
     * @return IRender return the renderer instance
     */
    function getRenderer(IRequest $Request) {
        return $this->getInstance()
            ->getRenderer($Request);
    }
}

class Routes_SelectorMap implements IRouteMap {

    private $mRequest;
    private $mDestination = null;
    private $mDone = false;
    private $mMatchedPath = null;
    private $mArgs = array();

    function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

    /**
     * @param IRenderAggregate $Destination
     * @param String $path
     * @param array $args
     */
    function getMatchedData(&$Destination, &$path, Array &$args) {
        $Destination = $this->mDestination;
        $path = $this->mMatchedPath;
        $args = $this->mArgs;
    }

    /**
     * @return IRenderAggregate
     */
    function getDestination() {
        return $this->mDestination;
    }

    /**
     * Map data to a key in the map
     * @param String $prefix
     * @param IRenderAggregate $Destination
     * @return bool if true the mapping will discontinue
     */
    function mapRoute($prefix, IRenderAggregate $Destination)
    {
        if($this->mDone)
            return false;
        list($method, $path) = explode(' ', $prefix, 2);
        $requestPath = $this->mRequest->getPath();
        if($method === 'ANY' || $method == $this->mRequest->getMethod()) {
            if(strpos($requestPath, $path) === 0) {
                $argPath = substr($requestPath, strlen($path));
                $args = explode('/', trim($argPath, '/'));
                $this->mDone = true;
                $this->mMatchedPath = $path;
                $this->mArgs = $args;
                if($Destination instanceof Routes_LazyRender)
                    $Destination = $Destination->getInstance();
                $this->mDestination = $Destination;
                //$Destination->render($this->mRequest);
                return true;
            }
        }

        return false;
    }


    function tryRenderDefault() {
        throw new \Exception("Route not found: " . $this->mRequest->getMethod() . ' ' . $this->mRequest->getPath());
    }
}