<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;
use CPath\Framework\Render\IRender;
use CPath\Framework\Render\IRenderAll;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Exceptions\CodedException;
use CPath\Framework\Response\Interfaces\IResponseCode;
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
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @throws Framework\Response\Exceptions\CodedException
     * @return String|void always returns void
     */
    function render(IRequest $Request) {
        $path = $Request->getPath();
        if(($ext = pathinfo($path, PATHINFO_EXTENSION))
            && in_array(strtolower($ext), array('js', 'css', 'png', 'gif', 'jpg', 'bmp', 'ico')))
            throw new CodedException("File request was passed to Script: ", IResponseCode::STATUS_NOT_FOUND);

        $Selector = new Routes_SelectorMap($Request);
        $this->mapRoutes($Selector);

        $newPath = '';
        $args = array();
        $Selector->getMatchedData($Destination, $newPath, $args);

//        if ($Destination instanceof IRoutable) {
//            // TODO: retreat from using IRoutable
//
//            $RouteUtil = new RouteUtil($Destination);
//            $RouteUtil->renderDestination($Request, $newPath, $args);
//
//        } else
        if ($Destination instanceof IRenderAggregate)
            $Destination = $Destination->getRenderer($Request, $newPath, $args);

        if($Destination instanceof IRender) {
            $Destination->render($Request);
        } else {
            throw new \Exception("No route");
        }
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
     * @param String $path the matched request path for this destination
     * @param String[] $args the arguments appended to the path
     * @return IRender return the renderer instance
     */
    function getRenderer(IRequest $Request, $path, $args) {
        return $this->getInstance()
            ->getRenderer($Request, $path, $args);
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
        if($method === 'ANY' || $method == $this->mRequest->getMethod()) {
            if(strpos($requestPath = $this->mRequest->getPath(), $path) === 0) {
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