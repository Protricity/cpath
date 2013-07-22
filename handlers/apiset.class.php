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
class APISet extends API implements IAPIParam, \ArrayAccess, \IteratorAggregate {

    const Build_Ignore = true;     // This class should not be built. Classes that use it should set Build_Ignore to false

    const Route_Methods = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const Route_Path = NULL;        // No custom route path. Path is based on namespace + class name

    const ROUTE_KEY = 'apiset_subroute';

    /** @var IAPI[] */
    protected $mAPIs = array();
    private $mPath = NULL;
    private $mClassName = NULL;
    private $mExecutedAPI = NULL;

    /**
     * Creates a new APISet instance
     * @param null $ContainerClass The class name of the class that created this APISet
     */
    public function __construct($ContainerClass=NULL) {
        if($ContainerClass)
            $this->mClassName = $ContainerClass;
        $this->addField(self::ROUTE_KEY, $this);
    }

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param mixed $value the input field to validate
     * @return mixed the formatted input field that passed validation
     * @throws InvalidRouteException if validation fails
     */
    public function validate($value)
    {
        $this->getAPI($value);
        return $value;
    }

    /**
     * @return String a description of the Api Field
     */
    public function getDescription()
    {
        // TODO: Implement getDescription() method.
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
     * @throws InvalidRouteException
     */
    public function getAPI($path=NULL) {
        if(!$path) $path = $this->mPath;
        $path = strtolower($path);
        if(!$path)
            throw new InvalidRouteException("Sub-route is missing. Possible routes are: ".implode(', ', array_keys($this->mAPIs)));
        if(!isset($this->mAPIs[$path]))
            throw new InvalidRouteException("Route '{$path}' is invalid. Possible routes are: ".implode(', ', array_keys($this->mAPIs)));
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
        if($path) $request[self::ROUTE_KEY] = $path;
        $request = $this->processRequest($request);
        $path = $request[self::ROUTE_KEY];
        unset($request[self::ROUTE_KEY]);
        /** @var API $API */
        $this->mExecutedAPI = $this->getAPI($path);
        $this->mExecutedAPI->mRoute = $this->mRoute;
        return $this->mExecutedAPI->executeAsResponse($request);
    }

//    function executeAsResponse(Array $request, $path=NULL) {
//        if($path != NULL)
//            $this->mPath = $path;
//        $API = $this;
//        if($this->mPath) {
//            $API = $this->getAPI();
//            $API->mRoute = $this->mRoute;
//        }
//        return $API->executeAsResponse($request);
//    }

    /**
     * Sends headers, executes the request, and renders an IResponse as HTML
     * @param array $request the request to execute
     * @return void
     */
    public function renderHTML(Array $request) {
        if(!headers_sent() && !Util::isCLI())
            header("Content-Type: text/html");
        $Render = new APIInfo();
        $Response = $this->executeAsResponse($request);
        $Response->sendHeaders();
        $Render->render($this->mExecutedAPI ?: $this, $Response);
    }

//    function render(IRoute $Route)
//    {
//        $this->mPath = $Route->getNextArg();
//        $this->getAPI($this->mPath);
//        $Route->addToRoute($this->mPath);
//        parent::render($Route);
//    }

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

class InvalidRouteException extends ValidationException {}