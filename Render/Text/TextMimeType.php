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
     * @param mixed $msg The log message
     * @param int $flags [optional] log flags
     * @return int the number of listeners that processed the log entry
     */
    function log($msg, $flags = 0) {
        if (!($flags & ~$this->mFlags)) {
            echo $msg . "\n";
            flush();
	        return 1;
        }
	    return 0;
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

