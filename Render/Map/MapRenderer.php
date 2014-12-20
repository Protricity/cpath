<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/12/2014
 * Time: 2:57 PM
 */
namespace CPath\Render\Map;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\Text\IRenderText;
use CPath\Render\XML\IRenderXML;
use CPath\Request\IRequest;

class MapRenderer implements IRenderHTML, IRenderText, IRenderJSON, IRenderXML
{
	private $mMap;
	private $mMapper;

	/**
	 * @param IKeyMap|ISequenceMap $Map
	 * @param IKeyMapper|ISequenceMapper $Mapper
	 */
	public function __construct($Map, $Mapper) {
		$this->mMap    = $Map;
		$this->mMapper = $Mapper;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$Mappable = $this->mMap;
		if ($Mappable instanceof IKeyMap) {
			$Mappable->mapKeys($this->mMapper);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Mappable->mapSequence($this->mMapper);
		}
	}

	/**
	 * Render request as JSON
	 * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderJSON(IRequest $Request) {
		$Mappable = $this->mMap;
		if ($Mappable instanceof IKeyMap) {
			$Mappable->mapKeys($this->mMapper);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Mappable->mapSequence($this->mMapper);
		}
	}

	/**
	 * Render request as plain text
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @return String|void always returns void
	 */
	function renderText(IRequest $Request) {
		$Mappable = $this->mMap;
		if ($Mappable instanceof IKeyMap) {
			$Mappable->mapKeys($this->mMapper);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Mappable->mapSequence($this->mMapper);
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
		$Mappable = $this->mMap;
		if ($Mappable instanceof IKeyMap) {
			$Mappable->mapKeys($this->mMapper);

		} elseif ($Mappable instanceof ISequenceMap) {
			$Mappable->mapSequence($this->mMapper);
		}
	}
}