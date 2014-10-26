<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/22/14
 * Time: 2:56 PM
 */
namespace CPath\Render\CLI\Templates;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Render\HTML\Element\HTMLLabel;
use CPath\Render\HTML\Element\HTMLTextAreaField;
use CPath\Render\HTML\Header\HeaderConfig;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Log\Render\HTMLLog;

class HTMLConsole implements IRenderHTML, IHTMLSupportHeaders
{

	private $mContainer;
	private $mHTMLLog;
	/** @var HTMLElement */
	private $mSpanMarker;
	/** @var HTMLInputField */
	private $mInputPath;
	/** @var HTMLInputField */
	private $mInputDomain;
	private $mInput;
	public function __construct($marker = '$') {
		$this->mContainer = new HTMLElement('div', 'html-console',
			$this->mHTMLLog = new HTMLLog('html-console-log'),
			$this->mSpanMarker = new HTMLElement('span', 'html-console-marker', $marker),
			$this->mInput = new HTMLInputField('text', '', 'text', 'html-console-input-text'),
			$this->mInputPath = new HTMLInputField('path', '', 'hidden', 'html-console-input-path'),
			$this->mInputDomain = new HTMLInputField('domain', '', 'hidden', 'html-console-input-domain')
		);
		//$this->mInput->setRows(1);
		$this->mHTMLLog->bindEventListener('.html-console');
	}

	/**
	 * @return HTMLLog
	 */
	public function getHTMLLog() {
		return $this->mHTMLLog;
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer instance to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeScript(HeaderConfig::$JQueryPath);
		$Head->writeScript(__DIR__ . '/assets/html-console.js');
		$Head->writeStyleSheet(__DIR__ . '/assets/html-console.css');
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null) {
		if(!$this->mInputPath->getValue())
			$this->mInputPath->setValue($Request->getPath());
		if(!$this->mInputDomain->getValue())
			$this->mInputDomain->setValue($Request->getDomainPath(true));

		//$this->getHTMLLog()->log($Request->getPath());
		$this->mContainer->renderHTML($Request, $Attr);
	}
}