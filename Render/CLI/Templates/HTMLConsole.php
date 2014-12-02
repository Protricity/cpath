<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/22/14
 * Time: 2:56 PM
 */
namespace CPath\Render\CLI\Templates;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\Form\HTMLFormField;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Header\HeaderConfig;
use CPath\Render\HTML\Header\IHeaderWriter;
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
	/** @var HTMLFormField */
	private $mInputPath;
	/** @var \CPath\Render\HTML\Element\Form\HTMLFormField */
	private $mInputDomain;
	private $mInput;
	public function __construct($marker = '$') {
		$this->mContainer = new HTMLElement('div', 'html-console',
			new HTMLElement('div', 'html-console-log',
				$this->mHTMLLog = new HTMLLog()
			),
			$this->mSpanMarker = new HTMLElement('span', 'html-console-marker', $marker),
			$this->mInput = new HTMLFormField('html-console-input-text', 'text'),
			$this->mInputPath = new HTMLFormField('html-console-input-path', 'path', null, 'hidden'),
			$this->mInputDomain = new HTMLFormField('html-console-input-domain', 'domain', null, 'hidden')
		);
		//$this->mInput->setRows(1);
		//$this->mHTMLLog->bindEventListener('.html-console');
		$this->mHTMLLog->setReverseOrder();
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
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Head->writeScript(HeaderConfig::$JQueryPath);
		$Head->writeScript(__DIR__ . '/assets/html-console.js');
		$Head->writeStyleSheet(__DIR__ . '/assets/html-console.css');
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		if(!$this->mInputPath->getRequestValue($Request))
			$this->mInputPath->setInputValue($Request->getPath());
		if(!$this->mInputDomain->getRequestValue($Request))
			$this->mInputDomain->setInputValue($Request->getDomainPath(true));

		//$this->getHTMLLog()->log($Request->getPath());
		$this->mContainer->renderHTML($Request, $Attr, $Parent);
	}
}