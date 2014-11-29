<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 11:45 PM
 */
namespace CPath\Data\Map;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLKeyMapRenderer;
use CPath\Render\HTML\HTMLSequenceMapRenderer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONKeyMapRenderer;
use CPath\Render\JSON\JSONSequenceMapRenderer;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextKeyMapRenderer;
use CPath\Render\Text\TextSequenceMapRenderer;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLKeyMapRenderer;
use CPath\Render\XML\XMLSequenceMapRenderer;
use CPath\Request\IRequest;

class MapRenderer implements IRenderHTML, IRenderXML, IRenderJSON, IRenderText, IHTMLSupportHeaders
{
	private $mMappable;
	public function __construct($Mappable) {
		$this->mMappable = $Mappable;
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param \CPath\Render\HTML\Header\IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new HTMLKeyMapRenderer($Request);
			$Renderer->writeHeaders($Request, $Head);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Renderer = new HTMLSequenceMapRenderer($Request);
			$Renderer->writeHeaders($Request, $Head);
		}
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new HTMLKeyMapRenderer($Request);
			$Mappable->mapKeys($Renderer);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Renderer = new HTMLSequenceMapRenderer($Request);
			$Mappable->mapSequence($Renderer);
		}
	}


	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new JSONKeyMapRenderer($Request);
			$Mappable->mapKeys($Renderer);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Renderer = new JSONSequenceMapRenderer($Request);
			$Mappable->mapSequence($Renderer);
		}
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new TextKeyMapRenderer($Request);
			$Mappable->mapKeys($Renderer);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Renderer = new TextSequenceMapRenderer($Request);
			$Mappable->mapSequence($Renderer);
		}
	}

	/**
	 * Render request as xml
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param string $rootElementName Optional name of the root element
	 * @param bool $declaration if true, the <!xml...> declaration will be rendered
	 * @return String|void always returns void
	 */
	function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
		$Mappable = $this->mMappable;
		if ($Mappable instanceof IKeyMap) {
			$Renderer = new XMLKeyMapRenderer($Request, $rootElementName, $declaration);
			$Mappable->mapKeys($Renderer);

		}

		if ($Mappable instanceof ISequenceMap) {
			$Renderer = new XMLSequenceMapRenderer($Request, $rootElementName, $declaration);
			$Mappable->mapSequence($Renderer);
		}
	}

}