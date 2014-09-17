<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:47 PM
 */
namespace CPath\Request\Common;

use CPath\Request\IRequest;

final class UnknownRequest implements IRequest
{

    private $mMethod = null;

    public function __construct($methodName=null) {
        $this->mMethod = $methodName ?: $_SERVER["REQUEST_METHOD"];
    }


    /**
     * Get the route path
     * @throws \Exception
     * @return String the route path starting with '/'
     */
    function getPath()
    {
        throw new \Exception("Unknown Request: " . $this->mMethod);
    }

    /**
     * Get the requested Mime types
     * @throws \Exception
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeTypes()
    {
        throw new \Exception("Unknown Request: " . $this->mMethod);
    }

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName()
    {
        return $this->mMethod;
    }

    /**
     * Get a parameter value by name or return null
     * @param string $name
     * @param $description
     * @throws \Exception
     * @return string|null the field parameter or null
     */
    function getParam($name, $description)
    {
        throw new \Exception("Unknown Request: " . $this->mMethod);
    }
}