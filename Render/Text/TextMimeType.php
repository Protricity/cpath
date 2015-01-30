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
	/** @var ILogListener[] */
	private $mLogListeners = array();

	private $mFlags;
    public function __construct($logFlags=NULL, $typeName='text/plain', IRequestedMimeType $nextMimeType=null) {
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
	    $c = 0;
        if (!$this->mFlags || !($flags & ~$this->mFlags)) {
            echo $msg . "\n";
            flush();
	        $c++;
        }
	    foreach($this->mLogListeners as $Log)
		    $c += $Log->log($msg, $flags);
	    return $c;
    }

	/**
	 * Add a log listener callback
	 * @param ILogListener $Listener
	 * @return void
	 * @throws \InvalidArgumentException if this log listener inst does not accept additional listeners
	 */
	function addLogListener(ILogListener $Listener) {
		if(!in_array($Listener, $this->mLogListeners))
			$this->mLogListeners[] = $Listener;
	}
}

