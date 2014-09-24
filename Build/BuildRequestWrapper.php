<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 11:12 PM
 */
namespace CPath\Build;

use CPath\Describable\IDescribable;
use CPath\Request\Exceptions\RequestParameterException;
use CPath\Request\Executable\IPrompt;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType;
use CPath\Request\RequestWrapper;

class BuildRequestWrapper extends RequestWrapper implements IBuildRequest
{
    private static $COUNT = 1;

    private $mRequest;
    private $mFlags;
    private $mID;
    /** @var ILogListener[] */
    private $mLogs = array();

    function __construct(IRequest $Request, $flags = null) {
        $this->mRequest = $Request;
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
     * @param String $msg The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function log($msg, $flags = 0) {
        foreach($this->mLogs as $Log)
            $Log->log($msg, $flags);
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