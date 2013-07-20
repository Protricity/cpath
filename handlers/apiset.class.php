<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Interfaces\IApi;
use CPath\NoRoutesFoundException;
use CPath\Util;
use CPath\Build;
use CPath\Interfaces\IResponse;
use CPath\Model\Response;
use CPath\Builders\BuildRoutes;

/**
 * Class ApiSet
 * @package CPath\Handlers
 *
 * Provides an Api collection
 */
class ApiSet extends Api {

    const BUILD_IGNORE = true;     // This class should not be built. Classes that use it should set BUILD_IGNORE to false

    const ROUTE_METHODS = 'GET|POST|CLI';     // Default accepted methods are GET and POST
    const ROUTE_PATH = NULL;        // No custom route path. Path is based on namespace + class name

    /** @var IApi[] */
    protected $mApis = array();
    private $mPath = NULL;

    /**
     * @param String $path the alphanumeric path to the IAPI Instance
     * @param IApi $Api the API instance
     * @return $this
     */
    public function addApi($path, IApi $Api) {
        $this->mApis[strtolower($path)] = $Api;
        return $this;
    }

    /**
     * @param String $path the api path to search
     * @return IApi the api instance or null if not found
     */
    public function getApi($path) {
        $path = strtolower($path);
        return isset($this->mApis[$path]) ? $this->mApis[$path] : NULL;
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
        if(!$path) $path = $this->mPath;
        if(!$path)
            throw new NoRoutesFoundException("Route is missing. Possible routes are: ".implode(', ', array_keys($this->mApis)));
        if(!isset($this->mApis[$path]))
            throw new NoRoutesFoundException("Route '{$path}' is missing invalid. Possible routes are: ".implode(', ', array_keys($this->mApis)));
        return $this->mApis[$path]->execute($request);
    }

    function executeAsResponse(Array $request, $path=NULL) {
        if($path != NULL)
            $this->mPath = $path;
        return parent::executeAsResponse($request);
    }


    function render(Array $args)
    {
        $this->mPath = strtolower(array_shift($args));
        parent::render($args);
    }
}
