<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Interfaces\IResponseHelper;
use CPath\Interfaces\IRoute;
use CPath\Util;
use CPath\Build;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IHandler;
use CPath\Model\MultiException;
use CPath\Model\Response;
use CPath\Model\ResponseException;
use CPath\Builders\RouteBuilder;
use CPath\Handlers\API\View\APIInfo;

/**
 * Class SimpleAPI
 * @package CPath
 *
 * Provides a portable Handler template for API calls
 */
class SimpleAPI extends API {

    const Build_Ignore = true;     // API Calls are built to provide routes

    private $mCallback;

    /**
     * @param Callable $callback
     * @param APIField[] $fields
     */
    public function __construct($callback, Array $fields=array()) {
        $this->mCallback = $callback;
        $this->addFields($fields);
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRoute $Route the IRoute instance for this render which contains the request and args
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    public function execute(IRoute $Route){
        $call = $this->mCallback;
        if($call instanceof \Closure)
            return $call($this, $Route);
        return call_user_func($call, $Route);
    }

}
