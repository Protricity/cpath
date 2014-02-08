<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;

use CPath\Framework\Request\Interfaces\IRequest;

class RoutableSetWrapper implements IRequest {

    private $mReq, $mRoute, $mRoutes;
    
    function __construct(IRequest $Request, RoutableSet $Routes, IRoute $NewRoute) {
        $this->mReq = $Request;
        $this->mRoute = $NewRoute;
        $this->mRoutes = $Routes;
    }

    function getRequest() { return $this->mReq; }
    function getRoute() { return $this->mRoute; }

    /** @return RoutableSet|IRoute[] */
    function getRoutableSet() { return $this->mRoutes; }
    function findRoute() { return $this->getRoute(); }

    function getIterator() { return $this->mReq->getIterator(); }
    function offsetExists($offset) { return $this->mReq->offsetExists($offset); }
    function offsetGet($offset) { return $this->mReq->offsetGet($offset); }
    function offsetSet($offset, $value) { $this->mReq->offsetSet($offset, $value); }
    function offsetUnset($offset) { $this->mReq->offsetUnset($offset); }
    function count() { return $this->mReq->count(); }
    function &getDataPath($_path = NULL) { return $this->mReq->getDataPath($_path); }
    function getPath() { return $this->mReq->getPath(); }
    function getMethod() { return $this->mReq->getMethod(); }
    function getRequestURL($withArgs = true, $withDomain = false) { return $this->mReq->getRequestURL($withArgs, $withDomain); }
    function getHeaders($key = NULL) { return $this->mReq->getHeaders($key); }
    function getNextArg($advance = true) { return $this->mReq->getNextArg($advance); }
    function getMimeTypes() { return $this->mReq->getMimeTypes(); }
    function merge(Array $request, $replace = false) { $this->mReq->merge($request, $replace); }
    function pluck($_path=NULL) { return $this->mReq->pluck($_path); } // TODO: fix
    function getFileUpload($_path=NULL) { return $this->mReq->getFileUpload($_path); } // TODO: fix
    // Static
    static function fromRequest() { throw new \InvalidArgumentException(); }
}