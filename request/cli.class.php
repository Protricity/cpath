<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Request;

use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Route\IRoute;
use CPath\Interfaces\IShortOptions;
use CPath\Log;
use CPath\LogException;
use CPath\Model\FileUpload;
use CPath\Route\MissingRoute;
use CPath\Route\Router;

class CLI extends AbstractRequest implements ILogListener, IShortOptions {

    private
        $mShortRequests = array();

    protected function __construct(Array $args) {
        if(!$args[0]) {
            $this->mMethod = 'CLI';
        } else {
            if(preg_match('/^('.str_replace(',', '|', IRoute::METHODS).')(?: (.*))?$/i', $args[0], $matches)) {
                array_shift($args);
                $this->mMethod = strtoupper($matches[1]);
                if(!empty($matches[2]))
                    array_unshift($args, $matches[2]);
            } else {
                $this->mMethod = 'CLI';
            }
        }

        $args2 = array();
        for($i=0; $i<sizeof($args); $i++) {
            if(is_array($args[$i])) {
                $this->mRequest = $args[$i] + $this->mRequest;
                continue;
            }
            $arg = trim($args[$i]);
            if($arg === '' || $arg === '-')
                continue;
            if($arg[0] == '-') {
                $val = true;
                if(!empty($args[$i+1]) && $args[$i+1][0] !== '-')
                    $val = $args[++$i];

                if($arg[1] == '-')
                    $this->mShortRequests[substr($arg, 2)] = $val;
                else
                    $this->mRequest[substr($arg, 1)] = $val;
            } else {
                $args2[] = $arg;
            }
        }
        $args = $args2;
        if($args) {
            if($args[0])
                foreach(array_reverse(explode('/', array_shift($args))) as $a)
                    if($a) array_unshift($args, $a);
            $parse = parse_url('/'.implode('/', $args));
            if(isset($parse['query'])) {
                parse_str($parse['query'], $query);
                $this->mRequest = $query + $this->mRequest;
            }
            $this->mPath = $parse['path'];
            //$this->mArgs = $args;
        } else {
            $this->mPath = '/';
            //$this->mArgs = array();
        }
    }

    public function setOutputLog($enable=true) {
        $enable ? Log::addCallback($this) : Log::removeCallback($this);
    }

    /**
     * Attempt to find a Route
     * @return \CPath\Route\IRoute the route instance found. MissingRoute is returned if no route was found
     */
    public function findRoute() {
        $routePath = $this->mMethod . ' ' . $this->mPath;
        $routePathAny = 'ANY ' . $this->mPath;
        $Route = Router::findRoute($routePath, $args)
            ?: Router::findRoute($routePathAny, $args)
            ?: new MissingRoute($routePath);
        $this->mRoute = $Route;
        $this->mArgs = $args;
        return $Route;
    }

    // Implement ILogListner

    function onLog(ILogEntry $log)
    {
        echo $log->getMessage(),"\n";
        if($log instanceof LogException)
            echo $log->getException();
    }

    /**
     * Returns a list of mimetypes accepted by this request
     * @return Array
     */
    function getMimeTypes() {
        return array('text/plain');
    }

    // Implement IShortOptions


    /**
     * Add or generate a short option for the list
     * @param String $fieldName the field name
     * @param String $shortName the short name representing the field name
     */
    function processShortOption($fieldName, $shortName) {
        if(isset($this->mShortRequests[$shortName]))
            $this->mRequest[$fieldName] = $this->mShortRequests[$shortName];
    }

    // Statics

    static function fromArgs($args, Array $request=NULL) {
        if(is_string($args))
            $args = explode(' ', $args);
        $CLI = new CLI($args);
        if($request)
            $CLI->merge($request);
        return $CLI;
    }

    static function fromRequest($force=false, $log=true) {
        static $CLI = NULL;
        if($CLI && !$force)
            return $CLI;
        $args = $_SERVER['argv'];
        array_shift($args);
        $CLI = new CLI($args);
        if($log)
            $CLI->setOutputLog(true);
        $CLI->mHeaders = function_exists('getallheaders')
            ? getallheaders()
            : array();
        return $CLI;
    }

    /**
     * Returns a file upload by name, or throw an exception
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getFileUpload(0, 'key') gets $_FILES[0]['key'] formatted as a FileUpload instance;
     * @return FileUpload|NULL a file upload instance or null if no file upload was found
     * @throws \Exception if the path was invalid
     */
    function getFileUpload($_path = NULL) {
        return null;
    }
}