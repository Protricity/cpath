<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/31/2015
 * Time: 3:17 PM
 */
namespace CPath\Render\HTML\Element\Table;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\IHTMLValueRenderer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLTableSequenceRenderer extends HTMLElement implements ISequenceMapper, IKeyMapper
{
	/** @var IHTMLValueRenderer */
	private $mValueRenderer;
	private $mMap;

	public function __construct(ISequenceMap $Map) {
		$this->mMap = $Map;
		parent::__construct('tbody');
	}

	public function addValueRenderer(IHTMLValueRenderer $Renderer) {
		$this->mValueRenderer = $Renderer;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $ContentAttr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		$this->mMap->mapSequence($this);
	}

	/**
	 * Map a value to a key in the map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String $key
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @return bool true to stop or any other value to continue
	 */
	function map($key, $value) {
		echo RI::ni(), "<td>", $this->mValueRenderer ? $this->mValueRenderer->renderNamedValue($key, $value) : $value, "</td>";
	}

	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param mixed $_arg additional varargs
	 * @return bool true to stop or any other value to continue
	 */
	function mapNext($value, $_arg = null) {
		echo RI::ni(), "<tr>";
		echo RI::ai(1);
		if ($value instanceof IKeyMap) {
			$value->mapKeys($this);

		} else {
			echo RI::ni(), "<td>", $this->mValueRenderer ? $this->mValueRenderer->renderValue($value) : $value, "</td>";
		}
		echo RI::ai(-1);
		echo RI::ni(), "</tr>";
	}
}