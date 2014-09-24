<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 2:16 PM
 */
namespace CPath\Request;

use CPath\Describable\IDescribable;
use CPath\Request\Log\ILogListener;
use CPath\Request\Web\CLIWebRequest;
use CPath\Request\Web\WebFormRequest;
use CPath\Request\Web\WebRequest;

abstract class Request implements IRequest
{
    private $mParams;
    /** @var ILogListener[] */
    private $mLogs=array();

    public function __construct($path=null, Array $params=array()) {
        $this->mPath = $path ? '/' . ltrim($path, '/') : '/';
        $this->mParams = $params;
    }

    /**
     * Checks a request value to see if it exists
     * @param string $paramName the parameter name
     * @return bool
     */
    function hasValue($paramName) {
        if(!empty($this->mParams[$paramName]))
            return true;

        return false;
    }

    /**
     * Get a request value by parameter name if it exists
     * @param string $paramName the parameter name
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @return mixed the parameter value or null
     */
    function getValue($paramName, $description = null) {
        if(!empty($this->mParams[$paramName]))
            return $this->mParams[$paramName];

        return null;
    }

    /**
     * Return the route path for this request
     * @return String the route path starting with '/'
     */
    function getPath() {
        return $this->mPath;
    }

    /**
     * Matches a route prefix to this request and updates the method args with any extra path
     * @param $routePrefix '[method] [path]'
     * @return bool true if the route matched
     */
    function match($routePrefix) {
        list($routeMethod, $path) = explode(' ', $routePrefix, 2);

        $requestMethod = $this->getMethodName();

//        if($this instanceof ILogListener)
//            $this->log("Matching " . $this->getPath() . " to " . $routePrefix);
        // /user/abc123/
        // /user/:id/
        if ($routeMethod !== 'ANY' && $routeMethod !== $requestMethod)
            return false;

        if(($p = strpos($path, ':')) !== false) {
            $routeArgs = explode('/', trim($path, '/'));
            $i=0;
            foreach(explode('/', trim($this->getPath(), '/')) as $requestPathArg) {
                if(!isset($routeArgs[$i]))
                    return false;

                $routeArg = $routeArgs[$i++];

                if($routeArg[0] == ':') {
                    $this->mParams[substr($routeArg, 1)] = $requestPathArg;

                } elseif(strcasecmp($routeArg, $requestPathArg) !== 0) {
                    return false;

                }
            }

            if(isset($routeArgs[$i])) // TODO: extra route return false?
                return false;

        } else {
            if (strcasecmp($this->getPath(), $path) !== 0)
                return false;

        }

            $this->log("Matched " . $routePrefix);
        return true;
    }

    // Static

    /**
     * Create a new IRequest instance from environment variables
     * @param String $route path string or route ([method] [path])
     * @param array $args
     * @return IRequest
     */
    public static function create($route=null, $args=array()) {
        $method = null;
        if(($p = strpos($route, ' ')) !== false)
            if($p <=5)
                list($method, $route) = explode(' ', $route, 2);
        //static $Inst = null;
        //if($Inst) return $Inst;

        if (PHP_SAPI === 'cli') {
            $Inst = new CLI\CLIRequest($route, $args);
        } else {
            if(!$method)
                $method = $_SERVER["REQUEST_METHOD"];
            if ($method === 'GET')
                $Inst = new WebRequest($method, $route, $args);
            elseif ($method === 'CLI')
                $Inst = new CLIWebRequest($route, $args);
            else
                $Inst = new WebFormRequest($method, $route, $args);
        }
        return $Inst;
    }



    /**
     * Add a log entry
     * @param String $msg The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function log($msg, $flags = 0) {
        foreach($this->mLogs as $Log)
            $Log->log($msg, $flags);

        $MimeType = $this->getMimeType();
        if($MimeType instanceof ILogListener)
            $MimeType->log($msg, $flags);

        //if($this->mVerbose || !($flags & ILogListener::VERBOSE))
        //echo $msg . "\n";
    }

    /**
     * Log an exception instance
     * @param \Exception $ex The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function logEx(\Exception $ex, $flags = 0) {
        foreach($this->mLogs as $Log)
            $Log->logEx($ex, $flags);

        $MimeType = $this->getMimeType();
        if($MimeType instanceof ILogListener)
            $MimeType->logEx($ex, $flags);
        //if($this->mVerbose || !($flags & ILogListener::VERBOSE))
        //echo $ex . "\n";
    }

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @return void
     */
    function addLogListener(ILogListener $Listener) {
        $this->mLogs[] = $Listener;
    }
}