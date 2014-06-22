<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 6/14/14
 * Time: 10:27 AM
 */
namespace CPath\Framework\Request\Common;

use CPath\Framework\Request\Interfaces\IRequest;

abstract class RequestWrapper implements IRequest
{
    /** @var IRequest */
    private $mRequest;

    public function __construct(IRequest $Request)
    {
        $this->mRequest = $Request;
    }

    public function getIterator()
    {
        return $this->mRequest->getIterator();
    }

    public function offsetGet($offset)
    {
        return $this->mRequest->offsetGet($offset);
    }

    public function offsetExists($offset)
    {
        return $this->mRequest->offsetExists($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->mRequest->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->mRequest->offsetUnset($offset);
    }

    function &getDataPath($_path = NULL)
    {
        return $this->mRequest->getDataPath($_path);
    }

    function getPath()
    {
        return $this->mRequest->getPath();
        //return $this->mPath ?: $this->mRequest->getPath();
    }

    function getArgs()
    {
        return $this->mRequest->getArgs();
    }

    function getMethod()
    {
        return $this->mRequest->getMethod();
        //return $this->mMethod ?: $this->mRequest->getMethod(); // TODO: shouldn't change
    }

    function getHeaders($key = NULL)
    {
        return $this->mRequest->getHeaders($key);
    }

    function getMimeTypes()
    {
        return $this->mRequest->getMimeTypes();
    }

    /**
     * Remove an element from the request array and return its value
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->pluck(0, 'key') removes $data[0]['key'] and returns it's value;
     * @return mixed the data array or targeted data specified by path
     * @throws \InvalidArgumentException if the data path doesn't exist
     */
    function pluck($_path)
    {
        return $this->mRequest->pluck($_path);
    }

    function merge(Array $request, $replace = false)
    {
        $this->mRequest->merge($request, $replace);
    }


    function getFileUpload($_path = NULL)
    {
        return $this->mRequest->getMimeTypes();
    }

    function getOriginalRequest()
    {
        return $this->mRequest;
    }

    public function count()
    {
        return $this->mRequest->count();
    }

}