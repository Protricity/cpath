<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/17/14
 * Time: 8:39 AM
 */
namespace CPath\Request\Log;

interface ILogListener
{
    const VERBOSE = 0x01; // Verbose message meant for the developers to see

    const WARNING = 0x10; // Warning log entry
    const ERROR = 0x20; // Error log entry

    /**
     * Add a log entry
     * @param mixed $msg The log message
     * @param int $flags [optional] log flags
     * @return int the number of listeners that processed the log entry
     */
    function log($msg, $flags = 0);

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @return void
     * @throws \InvalidArgumentException if this log listener inst does not accept additional listeners
     */
    function addLogListener(ILogListener $Listener);
}