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

/**
 * Class Log
 * @package CPath
 *
 * Provides logging functionality to all classes
 */
interface ILog extends IXML, IJSON {
    function getMessage();
}
abstract class Log implements ILog {
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
        $xml->addChild('msg', $this->getMessage())
            ->addAttribute('tag', $this->getTag());
    }

    // Statics

    private static $mLog = array();

    public static function add(ILog $Log) {
        self::$mLog[] = $Log;
    }

    /**
     * Log a verbose message. These are messages meant for the developer to see.
     * @param $tag string tag associated with this log entry
     * @param $msg string verbose message to log
     */
    public static function v($tag, $msg) {
        self::add(new LogVerbose((string)$tag, $msg));
    }

    /**
     * Log a user message. These are messages meant for the end-user to see.
     * @param $tag string tag associated with this log entry
     * @param $msg string user message to log
     */
    public static function u($tag, $msg) {
        self::add(new LogUser((string)$tag, $msg));
    }

    /**
     * Log an error message
     * @param $tag string tag associated with this log entry
     * @param $msg string error message to log
     */
    public static function e($tag, $msg) {
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
        self::add(new LogException((string)$tag, $ex, $msg));
    }

    /**
     * Return the entire log
     * @return array a list of log entries
     */
    public static function get() {
        return self::$mLog;
    }
}

class LogVerbose extends Log {}
class LogUser extends Log {}
class LogError extends Log {}
class LogException extends LogError {
    protected $mEx, $mTag;
    public function __construct($tag, \Exception $ex, $msg=NULL) {
        $this->mTag = $tag;
        $this->mEx = $ex;
        $this->mMsg = $msg ?: $ex->getMessage();
    }
    public function getException() { return $this->mEx; }
}