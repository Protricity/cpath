<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:45 PM
 */
namespace CPath\Request\CLI;

use CPath\Request\IRequest;
use CPath\Render\Text\TextMimeType;
use CPath\Request\IRequestMethod;

final class CLIRequest implements IRequest
{
    private $mPath;
    private $mMethod;
    private $mArgs;

    public function __construct(Array $argv = null) {
        if($argv === null) {
            $argv = $_SERVER['argv'];
            $file = array_shift($argv);
        }
        $this->mPath = array_shift($argv);
        $this->mArgs = CommandString::parseArgs($argv);
        $this->mMethod = new CLIMethod($this->mArgs);
    }

    /**
     * Matches a route prefix to this request
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix) {
        list($routeMethod, $path) = explode(' ', $routePrefix, 2);

        $requestMethod = $this->mMethod->getMethodName();

        // /user/abc123/
        // /user/:id/
        if ($routeMethod !== 'ANY' && $routeMethod == $requestMethod)
            return false;

        if(($p = strpos($path, ':')) !== false) {
            $routeArgs = explode('/', $path);
            $i=0;
            foreach(explode('/', $this->getPath()) as $requestPathArg) {
                if(!isset($routeArgs[$i]))
                    return false;

                $routeArg = $routeArgs[$i++];

                if($routeArg[0] == ':') {
                    $this->mArgs[substr($routeArg, 1)] = $requestPathArg;

                } elseif(strcasecmp($routeArg, $requestPathArg) !== 0) {
                    return false;

                }
            }

            if(isset($routeArgs[$i])) // TODO: extra route return false?
                return false;

            $this->mMethod = new CLIMethod($this->mArgs);

        } else {
            if (strcasecmp($this->getPath(), $path) !== 0)
                return false;

        }

        return true;
    }

    /**
     * Get the Request Method Instance (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return \CPath\Request\IRequestMethod
     */
    function getMethod() {
        return $this->mMethod;
    }

    /**
     * Get the route path
     * @return String the route path starting with '/'
     */
    function getPath() {
        return $this->mPath;
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeTypes() {
        return array(
            new TextMimeType(),
        );
    }
}