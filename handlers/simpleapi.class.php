<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;
use CPath\Build;
use CPath\Builders\RouteBuilder;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IRoute;
use CPath\Model\Response;
use CPath\Util;

/**
 * Class SimpleAPI
 * @package CPath
 *
 * Provides a portable Handler template for API calls
 */
class SimpleAPI extends API {

    private $mCallback;
    private $mDescription;

    /**
     * @param Callable $callback
     * @param APIField[] $fields
     * @param String $description
     */
    public function __construct($callback, Array $fields=array(), $description=NULL) {
        $this->mCallback = $callback;
        $this->addFields($fields);
        $this->mDescription = $description;
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupAPI() {}

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    final protected function doExecute(IRequest $Request) {
        $call = $this->mCallback;
        if($call instanceof \Closure)
            return $call($this, $Request);
        return call_user_func($call, $Request);
    }


    /**
     * Get the IAPI Description
     * @return String description for this IAPI
     */
    function getDescription() {
        return $this->mDescription ?: "No Description";
    }
}
