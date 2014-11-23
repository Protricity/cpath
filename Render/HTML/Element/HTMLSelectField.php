<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 10:15 AM
 */
namespace CPath\Render\HTML\Element;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\ISequenceMap;
use CPath\Data\Map\ISequenceMapper;
use CPath\Data\Map\SequenceMapCallback;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLSelectField extends AbstractHTMLElement implements ISequenceMap, IHTMLInput
{
	private $mValues = array();
	private $mSelected = array();

	/**
	 * @param null $name
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param null $_options [vararg] of options sent to ->addOption($arg)
	 */
	public function __construct($name = null, $classList = null, $_options = null) {
		parent::__construct('select', $classList);
		if($name)
			$this->setFieldName($name);
		$c = func_num_args();
		for($i=2; $i<$c; $i++) {
			$this->mValues[] = func_get_arg($i);
		}
	}

	public function addOption($value, $description=null, $selected=false) {
		if($description) {
			$this->mValues[] = array($description => $value);
		} else {
			$this->mValues[] = $value;
		}
		if($selected)
			$this->mSelected[] = $value;
	}

	public function getRequestValue(IRequest $Request) { return isset($this->mSelected[0]) ? $this->mSelected[0] : null; }

	public function setInputValue($value) {
		$this->select($value);
	}

	public function getFieldName() { return $this->getAttribute('name'); }
	public function setFieldName($name) { $this->setAttribute('name', $name); }

	public function getFieldID() { return $this->getAttribute('id'); }
	public function setFieldID($value) { $this->setAttribute('id', $value); }

	public function select($value, $_value=null) {
		$values = func_get_args();
		$selected=0;
		$this->mapSequence(new SequenceMapCallback(
				function (HTMLOptionMapper $Mapper) use ($values, &$selected) {
					foreach ($values as $value)
						if ($Mapper->isValue($value))
							$selected += $Mapper->select();
				}
			)
		);

		return $selected;
	}

	public function unSelect($value, $_value=null) {
		$c = sizeof($this->mSelected);
		$this->mSelected = array_diff($this->mSelected, func_get_args());
		return sizeof($this->mSelected) !== $c;
	}

	public function getSelectedValues() {
		return $this->mSelected;
	}

	public function isSelected($value) {
		return in_array($value, $this->mSelected);
	}

	/**
	 * Map sequential data to the map
	 * @param ISequenceMapper $Map
	 * @internal param \CPath\Request\IRequest $Request
	 */
	function mapSequence(ISequenceMapper $Map) {
		$Map = new HTMLOptionMapper($Map, $this->mSelected);

		foreach($this->mValues as $value) {
			if(is_array($value)) {
				foreach($value as $k=>$v) {
					$done = $Map->mapNext($v, is_int($k) ? null : $k);
					if($done === true)
						break;
				}

			} elseif ($value instanceof ISequenceMap) {
				$value->mapSequence($Map);

			} else {
				$done = $Map->mapNext($value);
				if($done === true)
					break;
			}
		}
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param \CPath\Render\HTML\Element\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		echo RI::ni(), "<", $this->getElementType(), $this->getAttributes()->render($Attr), ">";
		RI::ai(1);

		$this->mapSequence(new SequenceMapCallback(
				function (HTMLOptionMapper $Mapper) use ($Request) {
					$Mapper->renderHTML($Request);
				}
			)
		);

		RI::ai(-1);
		echo RI::ni(), "</", $this->getElementType(), ">";
	}
}

class HTMLOptionMapper implements ISequenceMapper, IRenderHTML {
	private $mMapper;
	private $mSelected;
	private $mArgs;
	private $mCount=0;

	public function __construct(ISequenceMapper $Map, Array &$selectedValues=array()) {
		$this->mMapper = $Map;
		$this->mSelected = &$selectedValues;
	}

	public function getValue() {
		return $this->mArgs[0];
	}

	public function isValue($value) {
		return $this->getValue() === $value;
	}

	public function getDescription() {
		return !empty($this->mArgs[1]) ? $this->mArgs[1] : $this->mArgs[0];
	}

	public function getCount() {
		return $this->mCount;
	}

	public function isSelected() {
		$value = $this->getValue();
		return in_array($value, $this->mSelected);
	}

	public function select() {
		if($this->isSelected())
			return false;
		$this->mSelected[] = $this->getValue();
		return true;
	}

	public function unSelect() {
		if(!$this->isSelected())
			return false;
		$this->mSelected = array_diff($this->mSelected, array($this->getValue()));
		return true;
	}

	/**
	 * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
	 * @param String|Array|IKeyMap|ISequenceMap $value
	 * @param null $description
	 * @return bool|void
	 */
	function mapNext($value, $description=null) {
		$this->mCount++;
		$this->mArgs = func_get_args();
		$this->mMapper->mapNext($this);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param \CPath\Render\HTML\Element\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		$value = $this->getValue();
		if($value instanceof IRenderHTML) {
			$value->renderHTML($Request, $Attr, $Parent);

		} else {
			$Option = new HTMLSelectOptionElement($this->getValue(), $this->getDescription(), $this->isSelected());
			$Option->renderHTML($Request, $Parent);

		}
	}
}