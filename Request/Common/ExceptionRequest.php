<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/19/14
 * Time: 8:51 PM
 */
namespace CPath\Request\Common;

use CPath\Request\IRequest;
use CPath\Request\RequestWrapper;

class ExceptionRequest extends RequestWrapper
{
    private $mEx;
    private $mRequest;

    function __construct(\Exception $Ex, IRequest $OriginalRequest) {
        $this->mEx = $Ex;
        parent::__construct($OriginalRequest);
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

}