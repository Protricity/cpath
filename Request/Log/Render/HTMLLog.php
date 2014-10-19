<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/18/14
 * Time: 3:32 PM
 */
namespace CPath\Request\Log\Render;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\RenderCallback;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;

class HTMLLog implements IRenderHTML, ILogListener, IHTMLSupportHeaders
{
	private $mLog = array();

	/**
	 * Add a log entry
	 * @param String $msg The log message
	 * @param int $flags [optional] log flags
	 * @return void
	 */
	function log($msg, $flags = 0) {
		$this->mLog[] = array($msg, $flags);
	}

	/**
	 * Log an exception instance
	 * @param \Exception $ex The log message
	 * @param int $flags [optional] log flags
	 * @return void
	 */
	function logEx(\Exception $ex, $flags = 0) {
		$this->mLog[] = array($ex, $flags);
	}

	/**
	 * Add a log listener callback
	 * @param ILogListener $Listener
	 * @return void
	 * @throws \InvalidArgumentException if this log listener instance does not accept additional listeners
	 */
	function addLogListener(ILogListener $Listener) {
		throw new \InvalidArgumentException("May not add listeners to " . __CLASS__);
	}

	/**
	 * Write all support headers used by this IView instance
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeScript(__DIR__ . '/assets/html-log.js');
		$Head->writeStyleSheet(__DIR__ . '/assets/html-log.css');
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		$Container = new HTMLElement('div', 'log-container');
		$THIS = $this;
		$Container->addContent(
			new RenderCallback(
				function(IRequest $Request, IAttributes $Attr=null) use ($THIS) {
					foreach($THIS->mLog as $log) {
						list($msg, $flags) = $log;
						if ($msg instanceof \Exception)
							$msg = $msg->getMessage();

						$Div = new HTMLElement('div', 'log-entry', $msg);
						if ($flags & ILogListener::VERBOSE)
							$Div->addClass('verbose');
						if ($flags & ILogListener::ERROR)
							$Div->addClass('error');
						$Div->renderHTML($Request);
					}
				}
			)
		);
		$Container->renderHTML($Request, $Attr);
	}
}