<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;
use CPath\Framework\Render\IRender;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Exceptions\CodedException;
use CPath\Framework\Response\Interfaces\IResponseCode;
use CPath\Framework\Route\Map\IRouteMap;
use CPath\Framework\Route\Routable\IRoutable;

class Routes implements IRoutable, IRender {

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
     * @return IRender found handler
     */
    function getHandlerFromRequest(IRequest $Request) {
        $Render = new Routes_SelectorMap($Request);
        $this->mapRoutes($Render);
        return $Render->getDestination();
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

        $Destination = $this->getHandlerFromRequest($Request);
        $Destination->render($Request);
    }
}

class Routes_LazyRender implements IRender {
    private $mDestination;
    public function __construct($destination) {
        $this->mDestination = $destination;
    }

    function getInstance() {
        /** @var IRender $Inst */
        $Inst = $this->mDestination;
        $Inst = new $Inst;
        return $Inst;
    }

    /**
     * Render this request
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request)
    {
        $this->getInstance()
            ->render($Request);
    }
}

class Routes_SelectorMap implements IRouteMap {

    private $mRequest, $mDestination = null, $mDone = false;

    function __construct(IRequest $Request) {
        $this->mRequest = $Request;
    }

    function getDestination() {
        return $this->mDestination;
    }

    /**
     * Map data to a key in the map
     * @param String $prefix
     * @param IRender $Destination
     * @return bool if true the mapping will discontinue
     */
    function mapRoute($prefix, IRender $Destination)
    {
        if($this->mDone)
            return false;
        list($method, $path) = explode(' ', $prefix, 2);
        if($method === 'ANY' || $method == $this->mRequest->getMethod()) {
            if(strpos($this->mRequest->getPath(), $path) === 0) {
                $this->mDone = true;
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