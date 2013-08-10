<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Interfaces\IJSON;
use CPath\Interfaces\IXML;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\ILogEntry;

/**
 * Class Log
 * @package CPath
 *
 * Provides logging functionality to all classes
 */
abstract class Log {

    const DEFAULT_LEVEL = LogVerbose::LEVEL;

    /** @var ILogEntry[] */
    //private static $mLog = array();
    /** @var ILogListener[] */
    private static $mCallbacks = array();

    private static $mLevel = NULL;

    public static function getDefaultLevel() {
        if(self::$mLevel !== NULL)
            return self::$mLevel;
        return self::$mLevel = Base::getConfig('log.level', self::DEFAULT_LEVEL);
    }

    public static function setDefaultLevel($level) {
        self::$mLevel = $level;
    }

    /**
     * Add a new log entry
     * @param ILogEntry $Log the log entry to add
     */
    public static function add(ILogEntry $Log) {
        $level = $Log::LEVEL;
        /** @var ILogListener $call */
        foreach(self::$mCallbacks as $l=>$calls) {
            if($l == -1)
                $l = self::getDefaultLevel();
            if($l >= $level)
                foreach($calls as $call)
                    $call->onLog($Log);
        }
    }

    /**
     * Add a logging callback to be performed until removeCallback() removes it
     * @param ILogListener $callback the callback to be performed
     * @param int|NULL $level the level of log types this handler should receive.
     */
    public static function addCallback(ILogListener $callback, $level=NULL) {
        if($level===NULL) $level = -1;
        self::removeCallback($callback);
        self::$mCallbacks[$level][] = $callback;
    }

    /**
     * Remove a callback
     * @param ILogListener $callback
     */
    public static function removeCallback(ILogListener $callback) {
        foreach(self::$mCallbacks as $l=>$calls)
            foreach($calls as $i=>$call)
                if($call === $callback)
                    unset(self::$mCallbacks[$l][$i]);
    }

    /**
     * Log a verbose (level 1) message. These are messages meant for the developer to see.
     * @param $tag string tag associated with this log entry
     * @param $msg string verbose message to log
     */
    public static function v($tag, $msg) {
        if(func_num_args()>2) $msg = vsprintf($msg, array_slice(func_get_args(), 2));
        self::add(new LogVerbose((string)$tag, $msg));
    }

    /**
     * Log a verbose (level 2) message. These are extra verbose messages meant for the developer to see.
     * @param $tag string tag associated with this log entry
     * @param $msg string verbose message to log
     */
    public static function v2($tag, $msg) {
        if(func_num_args()>2) $msg = vsprintf($msg, array_slice(func_get_args(), 2));
        self::add(new LogVerbose2((string)$tag, $msg));
    }

    /**
     * Log a user message. These are messages meant for the end-user to see.
     * @param $tag string tag associated with this log entry
     * @param $msg string user message to log
     */
    public static function u($tag, $msg) {
        if(func_num_args()>2) $msg = vsprintf($msg, array_slice(func_get_args(), 2));
        self::add(new LogUser((string)$tag, $msg));
    }

    /**
     * Log an error message
     * @param $tag string tag associated with this log entry
     * @param $msg string error message to log
     */
    public static function e($tag, $msg) {
        if(func_num_args()>2) $msg = vsprintf($msg, array_slice(func_get_args(), 2));
        error_log($tag."\t".$msg);
        self::add(new LogError((string)$tag, $msg));
    }

    /**
     * Log an exception message
     * @param $tag string tag associated with this log entry
     * @param $ex \Exception the thrown exception
     * @param $msg string exception message to log
     */
    public static function ex($tag, \Exception $ex, $msg=NULL) {
        if(func_num_args()>3) $msg = vsprintf($msg, array_slice(func_get_args(), 3));
        error_log($tag."\t".($msg ?: $ex->getMessage()));
        self::add(new LogException((string)$tag, $ex, $msg));
    }

    /**
     * Return the entire log
     * @return ILogEntry[] a list of log entries
     */
//    public static function get() {
//        return self::$mLog;
//    }
}

/**
 * Class LogEntry provides a basic implementation of ILogEntry
 * @package CPath
 */
abstract class LogEntry implements ILogEntry {
    protected $mMsg, $mTag;
    public function __construct($tag, $msg) { $this->mTag = $tag; $this->mMsg = $msg; }
    public function __toString() { return $this->getMessage(); }
    public function getMessage() { return $this->mMsg; }
    public function getTag() { return $this->mTag; }

    /**
     * Implements IJSON to convert the log entry to a jsonobject
     * @param array $JSON the existing json object to modify
     */
    function toJSON(Array &$JSON) {
        $JSON['tag'] = $this->getTag();
        $JSON['msg'] = $this->getMessage();
    }

    /**
     * Implements IXML to convert the log entry into an xml element
     * @param \SimpleXMLElement $xml the existing xml object
     */
    function toXML(\SimpleXMLElement $xml) {
        $xml[0] = $this->getMessage();
        $xml->addAttribute('tag', $this->getTag());
    }
}
/** Class LogVerbose - a verbose log entry for developers to see */
class LogVerbose extends LogEntry { const LEVEL = 3; }
/** Class LogVerbose2 - an extra verbose log entry for developers to see */
class LogVerbose2 extends LogEntry { const LEVEL = 4; }
/** Class LogUser - a status log entry for users to see */
class LogUser extends LogEntry { const LEVEL = 2; }
/** Class LogError - an error log entry */
class LogError extends LogEntry { const LEVEL = 1; }
/** Class LogException - an exception log entry */
class LogException extends LogError {
    const LEVEL = 1;
    protected $mEx, $mTag;
    public function __construct($tag, \Exception $ex, $msg=NULL) {
        $this->mTag = $tag;
        $this->mEx = $ex;
        $this->mMsg = $msg ?: $ex->getMessage();
    }
    public function getException() { return $this->mEx; }
}