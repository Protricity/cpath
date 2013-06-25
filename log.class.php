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

    /** @var ILogEntry[] */
    private static $mLog = array();
    /** @var ILogListener[] */
    private static $mCallbacks = array();

    public static function add(ILogEntry $Log) {
        self::$mLog[] = $Log;
        foreach(self::$mCallbacks as $i=>$call)
            $call->onLog($Log);
    }

    public static function addCallback(ILogListener $callback) {
        self::$mCallbacks[] = $callback;
    }

    public static function removeCallback(ILogListener $callback) {
        foreach(self::$mCallbacks as $i=>$call)
            if($call === $callback)
                unset(self::$mCallbacks[$i]);
    }

    /**
     * Log a verbose message. These are messages meant for the developer to see.
     * @param $tag string tag associated with this log entry
     * @param $msg string verbose message to log
     */
    public static function v($tag, $msg) {
        if(func_num_args()>2) $msg = vsprintf($msg, array_slice(func_get_args(), 2));
        self::add(new LogVerbose((string)$tag, $msg));
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
        self::add(new LogException((string)$tag, $ex, $msg));
    }

    /**
     * Return the entire log
     * @return ILogEntry[] a list of log entries
     */
    public static function get() {
        return self::$mLog;
    }
}

abstract class LogEntry implements ILogEntry {
    protected $mMsg, $mTag;
    public function __construct($tag, $msg) { $this->mTag = $tag; $this->mMsg = $msg; }
    public function __toString() { return $this->getMessage(); }
    public function getMessage() { return $this->mMsg; }
    public function getTag() { return $this->mTag; }

    function toJSON(Array &$JSON) {
        $JSON['tag'] = $this->getTag();
        $JSON['msg'] = $this->getMessage();
    }

    function toXML(\SimpleXMLElement $xml) {
        $xml->addAttribute('msg', $this->getMessage());
        $xml->addAttribute('tag', $this->getTag());
    }
}
class LogVerbose extends LogEntry {}
class LogUser extends LogEntry {}
class LogError extends LogEntry {}
class LogException extends LogError {
    protected $mEx, $mTag;
    public function __construct($tag, \Exception $ex, $msg=NULL) {
        $this->mTag = $tag;
        $this->mEx = $ex;
        $this->mMsg = $msg ?: $ex->getMessage();
    }
    public function getException() { return $this->mEx; }
}