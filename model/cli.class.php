<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;

use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\IRoute;
use CPath\LogException;

class CLI implements ILogListener {

    private $mArgs;
    private $mRequest = array();
    private $mUrl;

    public function __construct($args=NULL) {
        if($args===NULL) {
            $args = $_SERVER['argv'];
        } else {
            if(!is_array($args))
                $args = func_get_args();
            if(sizeof($args) == 1)
                $args = explode(' ', $args[0]);
        }

        if(!$args[0]) {
            $method = 'CLI';
        } else {
            if(preg_match('/^('.IRoute::METHODS.')(?: (.*))?$/i', $args[0], $matches)) {
                array_shift($args);
                $method = strtoupper($matches[1]);
                if(!empty($matches[2]))
                    array_unshift($args, $matches[2]);
            } else {
                $method = 'CLI';
            }
        }

        $args2 = array();
        for($i=0; $i<sizeof($args); $i++) {
            if(is_array($args[$i])) {
                $this->mRequest = $args[$i] + $this->mRequest;
                continue;
            }
            $arg = trim($args[$i]);
            if($arg === '')
                return;
            if($arg[0] == '-') {
                $val = true;
                if(!empty($args[$i+1]) && $args[$i+1][0] !== '-')
                    $val = $args[++$i];

                $this->mRequest[ltrim($arg, '- ')] = $val;
            } else {
                $args2[] = $arg;
            }
        }
        $args = $args2;

        if($args) {
            if($args[0])
                foreach(array_reverse(explode('/', array_shift($args))) as $a)
                    if($a) array_unshift($args, $a);
            $this->mUrl = parse_url('/'.implode('/', $args));
            $this->mArgs = $args;
        } else {
            $this->mUrl = array('path'=>'/');
            $this->mArgs = array();
        }

        if(isset($this->mUrl['query'])) {
            $query = array();
            parse_str($this->mUrl['query'], $query);
            $this->mRequest = $query + $this->mRequest;
        }

        $this->mUrl['method'] = $method;
        $this->mUrl['route'] = $method . " " . $this->mUrl['path'];
    }

    public function getArgs() { return $this->mArgs; }
    public function getRequest() { return $this->mRequest; }
    public function getParsedUrl() { return $this->mUrl; }
    public function getPath() { return $this->mUrl['path']; }
    public function getRoute() { return $this->mUrl['route']; }

    function onLog(ILogEntry $log)
    {
        echo $log->getMessage(),"\n";
        if($log instanceof LogException)
            echo $log->getException();
    }
}