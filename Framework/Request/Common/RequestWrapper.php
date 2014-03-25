<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/24/14
 * Time: 4:06 PM
 */
namespace CPath\Framework\Request\Common;

use CPath\Base;
use CPath\Framework\Data\Wrapper\IWrapper;
use CPath\Framework\Request\Interfaces\IRequest;

class RequestWrapper implements IRequest, IWrapper
{
    private $mRequest;
    private $mPath, $mArgs, $mMethod;

    public function __construct(IRequest $Request, $newPrefix=null, $newArgs=null) {
        list($newMethod, $newPath) = explode(' ', $newPrefix, 2);
        $newPath = rtrim($newPath, '/') . '/';
        $this->mPath = $newPath;
        $this->mMethod = $newMethod;
        $this->mArgs = $newArgs;
        $this->mRequest = $Request;
    }

    public function getIterator() {
        return $this->mRequest->getIterator();
    }

    public function offsetGet($offset){
        return $this->mRequest->offsetGet($offset);
    }

    public function offsetExists($offset) {
        return $this->mRequest->offsetExists($offset);
    }

    public function offsetSet($offset, $value) {
        $this->mRequest->offsetSet($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->mRequest->offsetUnset($offset);
    }

    function &getDataPath($_path = NULL) {
        return $this->mRequest->getDataPath($_path);
    }

    function getMatchedPath() {
        return $this->mPath;
    }

    function getPath() {
        return $this->mRequest->getPath();
        //return $this->mPath ?: $this->mRequest->getPath();
    }

    function getArgs() {
        return $this->mArgs ?: $this->mRequest->getArgs();
    }

    function getMatchedMethod() {
        return $this->mMethod;
    }

    function getMethod() {
        return $this->mRequest->getMethod();
        //return $this->mMethod ?: $this->mRequest->getMethod(); // TODO: shouldn't change
    }

    function getHeaders($key = NULL) {
        return $this->mRequest->getHeaders($key);
    }

    function getMimeTypes() {
        return $this->mRequest->getMimeTypes();
    }

    /**
     * Remove an element from the request array and return its value
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->pluck(0, 'key') removes $data[0]['key'] and returns it's value;
     * @return mixed the data array or targeted data specified by path
     * @throws \InvalidArgumentException if the data path doesn't exist
     */
    function pluck($_path) {
        return $this->mRequest->pluck($_path);
    }

    function merge(Array $request, $replace = false) {
        $this->mRequest->merge($request, $replace);
    }


    function getFileUpload($_path = NULL) {
        return $this->mRequest->getMimeTypes();
    }

    function getWrappedObject() {
        return $this->mRequest;
    }

    static function fromRequest($newPath=null, $newArgs=null) {
        return new RequestWrapper(Base::getRequest(), $newPath, $newArgs);
    }

    public function count() {
        return $this->mRequest->count();
    }
}