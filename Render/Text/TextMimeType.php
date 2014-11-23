<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Render\Text;

use CPath\Request\Log\ILogListener;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\MimeType;

class TextMimeType extends MimeType implements ILogListener
{
    private $mFlags;
    public function __construct($logFlags=0, $typeName='text/plain', IRequestedMimeType $nextMimeType=null) {
        $this->mFlags = $logFlags;
        parent::__construct($typeName, $nextMimeType);
    }

    /**
     * Add a log entry
     * @param String $msg The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function log($msg, $flags = 0) {
//        if(!$this->headersSent())
//            $this->sendHeaders(); // TODO: Sending 200 here by default

        if (!($flags & ~$this->mFlags)) {
            echo $msg . "\n";
            flush();
        }
    }

    /**
     * Log an exception inst
     * @param \Exception $ex The log message
     * @param int $flags [optional] log flags
     * @return void
     */
    function logEx(\Exception $ex, $flags = 0) {
//        if(!$this->headersSent())
//            $this->sendHeaders(); // TODO: Sending 200 here by default

        if (!($flags & ~$this->mFlags)) {
            echo $ex . "\n";
            flush();
        }
    }

    /**
     * Add a log listener callback
     * @param ILogListener $Listener
     * @throws \InvalidArgumentException
     * @return void
     */
    function addLogListener(ILogListener $Listener) {
        throw new \InvalidArgumentException("Can't add listeners to this mimetype. Add it to the IRequest instead");
    }
}

