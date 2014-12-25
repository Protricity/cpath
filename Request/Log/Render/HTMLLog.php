<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/18/14
 * Time: 3:32 PM
 */
namespace CPath\Request\Log\Render;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;

class HTMLLog implements IRenderHTML, ILogListener
{
	private $mLog = array();
	private $mReverseOrder = false;
	/** @var ILogListener[] */
	private $mLogListeners = array();

	//const CSS_REVERSE_ORDER = 'reverse-order';

	public function __construct() {
	}

	function setReverseOrder($reverse=true) {
		$this->mReverseOrder = $reverse;
	}

	function clearLog() {
		$this->mLog = array();
	}

	/**
	 * Add a log entry
	 * @param mixed $msg The log message
	 * @param int $flags [optional] log flags
	 * @return int the number of listeners that processed the log entry
	 */
	function log($msg, $flags = 0) {
		$c = 1;
		$this->mLog[] = array($msg, $flags);
		foreach($this->mLogListeners as $Listener)
			$c += $Listener->log($msg, $flags);
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

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$logs = $this->mLog;
		if($this->mReverseOrder)
			rsort($logs);

		foreach($logs as $log) {
			list($msg, $flags) = $log;
			if ($msg instanceof \Exception) {
				$msg = $msg->getMessage();
				$flags |= static::ERROR;
			}

			$Div = new HTMLElement('div', null, $msg);
			if ($flags & static::VERBOSE)
				$Div->addClass('verbose');
			if ($flags & static::ERROR)
				$Div->addClass('error');
			$Div->renderHTML($Request, $Attr);
		}
	}
}