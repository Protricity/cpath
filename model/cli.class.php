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

    private $mMethod;
    private $mArgs;
    private $mRequest = array();
    private $mUrl;

    public function __construct(Array $args=NULL) {
        if($args===NULL)
            $args = $_SERVER['argv'];

        if(!$args[0]) {
            $this->mMethod = 'CLI';
        } else {
            if(preg_match('/^get|post|cli$/i', $args[0])) {
                $this->mMethod = strtoupper(array_shift($args));
            } else {
                $this->mMethod = 'CLI';
            }
        }

        $args2 = array();
        for($i=0; $i<sizeof($args); $i++) {
            if($args[$i][0] == '-') {
                $this->mRequest[ltrim($args[$i], '- ')] = $args[++$i];
            } else {
                $args2[] = $args[$i];
            }
        }
        $this->mArgs = $args2;

        if(isset($args2[0]) && $args2[0][0] == '/')
            $this->mUrl = parse_url($args2[0]);
        else
            $this->mUrl = array('path'=>'/'.implode('/', $args2));

        if(isset($this->mUrl['query'])) {
            $query = array();
            parse_str($this->mUrl['query'], $query);
            $this->mRequest = $query + $this->mRequest;
        }

        $this->mUrl['method'] = $this->mMethod;
    }

    public function getArgs() { return $this->mArgs; }
    public function getRequest() { return $this->mRequest; }
    public function getParsedUrl() { return $this->mUrl; }
    public function getMethod() { return $this->mMethod; }
    public function getPath() { return $this->mUrl['path']; }

    function onLog(ILogEntry $log)
    {
        echo $log->getMessage(),"\n";
    }
}