<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/17/14
 * Time: 8:39 AM
 */
namespace CPath\Request\Log;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

interface ILogListener
{
    const VERBOSE = 0x01; // Verbose message meant for the developers to see

    const WARNING = 0x10; // Warning log entry
    const ERROR = 0x20; // Error log entry

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

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @return void
     * @throws \InvalidArgumentException if this log listener instance does not accept additional listeners
     */
    function addLogListener(ILogListener $Listener);
}