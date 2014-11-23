<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/20/14
 * Time: 2:18 PM
 */
namespace CPath\Data\Map;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLSequenceMapRenderer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONSequenceMapRenderer;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextSequenceMapRenderer;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLSequenceMapRenderer;
use CPath\Request\IRequest;

class SequenceMapRenderer implements IRenderHTML, IRenderXML, IRenderJSON, IRenderText, IHTMLSupportHeaders
{
	private $mSequencemap;

	public function __construct(ISequenceMap $SequenceMap) {
		$this->mSequencemap = $SequenceMap;
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return String|void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Renderer = new HTMLSequenceMapRenderer($Request);
		$Renderer->writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$Renderer = new HTMLSequenceMapRenderer($Request);
		$this->mSequencemap->mapSequence($Renderer);
	}


	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request) {
		$Renderer = new JSONSequenceMapRenderer($Request);
		$this->mSequencemap->mapSequence($Renderer);
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request) {
		$Renderer = new TextSequenceMapRenderer($Request);
		$this->mSequencemap->mapSequence($Renderer);
	}

	/**
	 * Render request as xml
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param string $rootElementName Optional name of the root element
	 * @param bool $declaration if true, the <!xml...> declaration will be rendered
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
		$Renderer = new XMLSequenceMapRenderer($Request, $rootElementName, $declaration);
		$this->mSequencemap->mapSequence($Renderer);
	}
}