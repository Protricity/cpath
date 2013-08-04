<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Misc;

use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\ILogListener;
use CPath\Log;

class SimpleLogger implements ILogListener {
    /** @var ILogEntry[] */
    private $mLogs = array();
    private $mLevel = NULL;

    function __construct($startCapture=false, $level=NULL) {
        $this->mLevel = $level;
        if($startCapture)
            Log::addCallback($this, $level);
    }

    /**
     * Start capturing logs
     * @param bool $enable true to enable or false to disable
     * @param int|NULL $level the log level to capture
     */
    function capture($enable=true, $level=NULL) {
        if($enable)
            Log::addCallback($this, $level !== NULL ? $level : $this->mLevel);
        else
            Log::removeCallback($this);
    }

    function onLog(ILogEntry $log) {
        $this->mLogs[] = $log;
    }

    /**
     * Get captured logs
     * @return ILogEntry[]
     */
    public function getLogs() {
        return $this->mLogs;
    }
}