<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Handlers\API\View\APIInfo;
use CPath\Interfaces\IAPI;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRoute;
use CPath\NoRoutesFoundException;
use CPath\Route;
use CPath\Util;
use CPath\Build;
use CPath\Interfaces\IResponse;
use CPath\Model\Response;
use CPath\Builders\BuildRoutes;

/**
 * Class APISet
 * @package CPath\Handlers
 *
 * Provides an API collection
 */
class APISet extends API implements \ArrayAccess, \IteratorAggregate {

    const BUILD_IGNORE = true;     // This class should not be built. Classes that use it should set BUILD_IGNORE to false

    const ROUTE_METHODS = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;        // No custom route path. Path is based on namespace + class name

    /** @var IAPI[] */
    protected $mAPIs = array();
    private $mPath = NULL;
    private $mClassName = NULL;

    /**
     * Creates a new APISet instance
     * @param null $ContainerClass The class name of the class that created this APISet
     */
    public function __construct($ContainerClass=NULL) {
        if($ContainerClass)
            $this->mClassName = $ContainerClass;
    }

    /**
     * @param String $path the alphanumeric path to the IAPI Instance
     * @param IAPI $API the API instance
     * @return $this
     */
    public function addAPI($path, IAPI $API) {
        $this->mAPIs[strtolower($path)] = $API;
        return $this;
    }

    /**
     * @param String $path the api path to search. If null, the currently selected API is used
     * @return IAPI the api instance or null if not found
     * @throws \CPath\NoRoutesFoundException
     */
    public function getAPI($path=NULL) {
        if(!$path) $path = $this->mPath;
        $path = strtolower($path);
        if(!$path)
            throw new NoRoutesFoundException("Sub-route is missing. Possible routes are: ".implode(', ', array_keys($this->mAPIs)));
        if(!isset($this->mAPIs[$path]))
            throw new NoRoutesFoundException("Route '{$path}' is invalid. Possible routes are: ".implode(', ', array_keys($this->mAPIs)));
        return $this->mAPIs[$path];
    }

    /**
     * Execute the API Endpoint based on the given path with the entire request.
     * If running directly, the path may be specified in the $path parameter
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @param String|null $path
     * @return IResponse the api call response with data, message, and status
     * @throws NoRoutesFoundException if the api path was not found
     */
    function execute(Array $request, $path=NULL)
    {
        /** @var API $API */
        $API = $this->getAPI($path);
        $API->mRoute = $this->mRoute;
        $API->parseRequestParams($request, $this->mRoute);
        return $API->execute($request);
    }

    function executeAsResponse(Array $request, $path=NULL) {
        if($path != NULL)
            $this->mPath = $path;
        return parent::executeAsResponse($request);
    }

    /**
     * Sends headers and renders an IResponse as HTML
     * @param IResponse $Response
     * @return void
     */
    public function renderHTML(IResponse $Response) {
        $API = $this;
        if($this->mPath)
            $API = $this->getAPI();
        $Response->sendHeaders('text/html');
        $Render = new APIInfo();
        $Render->render($API, $Response);
    }

    function render(IRoute $Route)
    {
        $this->mPath = $Route->getNextArg();
        $this->getAPI($this->mPath);
        $Route->addToRoute($this->mPath);
        parent::render($Route);
    }

    // Implement ArrayAccess

    public function offsetExists($path) { return isset($this->mAPIs[$path]);}

    /**
     * Shortcut for getAPI($path)
     * @param mixed $path
     * @return IAPI
     */
    public function offsetGet($path) { return $this->getAPI($path);}
    public function offsetSet($path, $value) { $this->addAPI($path, $value); }
    public function offsetUnset($path) { unset($this->mAPIs[$path]); }

    // Implement IteratorAggregate

    public function getIterator() { return new \ArrayIterator($this->mAPIs); }
}
