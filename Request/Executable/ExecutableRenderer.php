<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 8:33 PM
 */
namespace CPath\Request\Executable;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Handlers\Response\ResponseUtil;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\Text\IRenderText;
use CPath\Render\XML\IRenderXML;
use CPath\Request\IRequest;
use CPath\Response\Response;


class ExecutableRenderer implements IRenderHTML, IRenderJSON, IRenderXML, IRenderText, IHTMLSupportHeaders {

	private $mExecutable, $mResponse=null;
    public function __construct(IExecutable $Executable) {
        $this->mExecutable = $Executable;
    }

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param \CPath\Render\HTML\IRenderHTML|\CPath\Request\Executable\IHTMLContainer $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$Response = $this->mResponse ?: $this->mExecutable->execute($Request);
		if(!$Response instanceof IRenderHTML)
			$Response = new ResponseUtil($Response);
		$Response->renderHTML($Request, $Attr);
		unset($this->mResponse);
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Response = $this->mResponse ?: $this->mExecutable->execute($Request);
		if($Response instanceof IHTMLSupportHeaders)
			$Response->writeHeaders($Request, $Head);
		$this->mResponse = $Response;
	}

	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request) {
		$Response = $this->mExecutable->execute($Request)
			?: new Response("No Response", false);
		if(!$Response instanceof IRenderJSON)
			$Response = new ResponseUtil($Response);
		$Response->renderJSON($Request);
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request) {
		$Response = $this->mExecutable->execute($Request);
		if(!$Response instanceof IRenderText)
			$Response = new ResponseUtil($Response);
		$Response->renderText($Request);
	}

	/**
	 * Render request as xml
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param string $rootElementName Optional name of the root element
	 * @param bool $declaration if true, the <!xml...> declaration will be rendered
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
		$Response = $this->mExecutable->execute($Request);
		if(!$Response instanceof IRenderXML)
			$Response = new ResponseUtil($Response);
		$Response->renderXML($Request);
	}
}