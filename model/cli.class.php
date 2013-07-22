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

class CLI implements ILogListener {

    private $mArgs;
    private $mRequest = array();
    private $mUrl;

    public function __construct(Array $args=NULL) {
        if($args===NULL)
            $args = $_SERVER['argv'];

        if(!$args[0]) {
            $method = 'CLI';
        } else {
            if(preg_match('/^get|post|cli$/i', $args[0])) {
                $method = strtoupper(array_shift($args));
            } else {
                $method = 'CLI';
            }
        }

        $args2 = array();
        for($i=0; $i<sizeof($args); $i++) {
            $arg = trim($args[$i]);
            if($arg === '')
                return;
            if($arg[0] == '-') {
                if($arg[$i+1]) {
                    if($arg[$i+1][0] == '-')
                        $val = true;
                    else
                        $val = $arg[++$i];
                    $this->mRequest[ltrim($arg, '- ')] = $val;
                }
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
    }
}