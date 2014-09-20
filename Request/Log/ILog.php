<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/17/14
 * Time: 8:39 AM
 */
namespace CPath\Request\Log;

interface ILog
{
    const ERROR = 0x1; // Error log entry
    const VERBOSE = 0x2; // Verbose message meant for the developers to see

    /**
     * Add a log entry
     * @param String $msg The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function log($msg, $flags = 0);

    /**
     * Log an exception instance
     * @param \Exception $ex The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function logEx(\Exception $ex, $flags = 0);
}