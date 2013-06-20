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
    protected $mMsg;
    public function __construct($msg) { $this->mMsg = $msg; }
    public function __toString() { return $this->getMessage(); }
    public function getMessage() { return $this->mMsg; }

    function toJSON(Array &$JSON) {
        $JSON['msg'] = $this->getMessage();
    }

    function toXML(\SimpleXMLElement $xml) {
        $xml->addChild('msg', $this->getMessage());
    }

    // Statics

    private static $mLog = array();

    public static function add(ILog $Log) {
        self::$mLog[] = $Log;
    }

    /**
     * Log a verbose message. These are messages meant for the developer to see.
     * @param $msg string verbose message to log
     */
    public static function v($msg) {
        self::add(new LogVerbose($msg));
    }

    /**
     * Log a user message. These are messages meant for the end-user to see.
     * @param $msg string user message to log
     */
    public static function u($msg) {
        self::add(new LogUser($msg));
    }

    /**
     * Log an error message
     * @param $msg string error message to log
     */
    public static function e($msg) {
        self::add(new LogError($msg));
    }

    /**
     * Log an exception message
     * @param $msg string exception message to log
     */
    public static function ex(\Exception $ex, $msg=NULL) {
        self::add(new LogException($ex, $msg));
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
    protected $mEx;
    public function __construct(\Exception $ex, $msg=NULL) {
        $this->mEx = $ex;
        $this->mMsg = $msg ?: $ex->getMessage();
    }
    public function getException() { return $this->mEx; }
}