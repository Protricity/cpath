<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/19/14
 * Time: 8:51 PM
 */
namespace CPath\Request\Common;

use CPath\Request\IRequest;
use CPath\Request\IRequestMethod;

class ExceptionRequest implements IRequest
{
    private $mEx;
    private $mRequest;

    function __construct(\Exception $Ex, IRequest $OriginalRequest) {
        $this->mEx = $Ex;
        $this->mRequest = $OriginalRequest;
    }

    public function getOriginalRequest() {
        return $this->mRequest;
    }

    public function getException() {
        return $this->mEx;
    }

    /**
     * Matches a route prefix to this request
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix) {
        if (strpos($routePrefix, 'ERR') !== 0)
            return false;

        list(, $path) = explode(' ', $routePrefix, 2);

        if (strpos($this->getPath(), $path) !== 0)
            return false;

        return true;
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeTypes() {
        return $this->mRequest->getMimeTypes();
    }

    /**
     * Get the Request Method Instance (ERR)
     * @return IRequestMethod
     */
    function getMethod() {
        return $this->mRequest->getMethod();
    }

    /**
     * Return the route path for this request
     * @return String the route path starting with '/'
     */
    function getPath() {
        return $this->mRequest->getPath();
    }
}