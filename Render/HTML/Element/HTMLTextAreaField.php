<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 10:38 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

class HTMLTextAreaField extends AbstractHTMLElement implements IHTMLInput
{
	const TRIM_CONTENT = true;
	const NODE_TYPE = 'textarea';

	private $mText;

	/**
	 * @param null $name
	 * @param null $value
	 * @param String|Array|IAttributes $classList attribute instance, class list, or attribute html
	 */
	public function __construct($name = null, $value = null, $classList = null) {
		parent::__construct(static::NODE_TYPE, $classList);
		if($value)
			$this->setInputValue($value);
		if($name)
			$this->setFieldName($name);
	}

	public function getInputValue(IRequest $Request)          { return $this->mText; }
	public function setInputValue($text)     { $this->mText = $text; }

	public function getFieldName()           { return $this->getAttribute('name'); }
	public function setFieldName($name)     { $this->setAttribute('name', $name); }

	public function getType()           { return $this->getAttribute('type'); }
	public function setType($value)     { $this->setAttribute('type', $value); }

	public function getFieldID()             { return $this->getAttribute('id'); }
	public function setFieldID($value)       { $this->setAttribute('id', $value); }

	public function setRows($rowCount) {
		$this->setAttribute('rows', $rowCount);
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
		echo $this->mText;
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return true;
	}
}