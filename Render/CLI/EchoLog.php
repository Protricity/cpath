<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/22/14
 * Time: 8:11 PM
 */
namespace CPath\Render\CLI;

use CPath\Request\Log\ILogListener;

class EchoLog implements ILogListener
{
    private $mFlags = 0;

    public function __construct($flags = 0) {
        $this->mFlags = $flags;
    }

    /**
     * Add a log entry
     * @param String $msg The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function log($msg, $flags = 0) {
        if (!($flags & ~$this->mFlags))
            echo $msg . "\n";
    }

    /**
     * Log an exception instance
     * @param \Exception $ex The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function logEx(\Exception $ex, $flags = 0) {
        if (!($flags & ~$this->mFlags))
            echo $ex . "\n";
    }

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @throws \InvalidArgumentException
     * @return void
     */
    function addLogListener(ILogListener $Listener) {
        throw new \InvalidArgumentException("Cannot add ILogListener to " . __CLASS__);
    }
}