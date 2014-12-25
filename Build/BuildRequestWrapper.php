<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 11:12 PM
 */
namespace CPath\Build;

use CPath\Request\AbstractRequestWrapper;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\MimeType;

class BuildRequestWrapper extends AbstractRequestWrapper implements IBuildRequest
{
    private static $COUNT = 1;

    private $mFlags;
    private $mID;
    /** @var ILogListener[] */
    private $mLogListeners = array();

    function __construct(IRequest $Request, $flags = null) {
        parent::__construct($Request);
        $this->mFlags = $flags;
        $this->mID = ++self::$COUNT;
        if($Request instanceof ILogListener)
            $this->addLogListener($Request);
    }

    function getBuildID() {
        return $this->mID;
    }

    /**
     * Test values for one or more flags
     * @param String $_flag vararg of flags.
     * ->hasFlag(FLAG1 | FLAG2, FLAG3) returns true IF (either FLAG1 OR FLAG2 is set) AND (FLAG3 is set)
     * @return bool
     */
    function hasFlag($_flag) {
        foreach(func_get_args() as $arg)
            if(!($arg & $this->mFlags))
                return false;

        return true;
    }

    /**
     * Add a log entry
     * @param mixed $msg The log message
     * @param int $flags [optional] log flags
     * @return int the number of listeners that processed the log entry
     */
    function log($msg, $flags = 0) {
	    $c = 0;
        foreach($this->mLogListeners as $Log)
            $c += $Log->log($msg, $flags);
	    return $c;
    }

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @return void
     */
    function addLogListener(ILogListener $Listener) {
	    if(!in_array($Listener, $this->mLogListeners))
		    $this->mLogListeners[] = $Listener;
    }
}