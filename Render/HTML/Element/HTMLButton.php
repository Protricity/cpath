<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 1:56 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLButton extends AbstractHTMLElement implements IHTMLInput
{
	const TRIM_CONTENT = true;
	private $mContent;

	/**
	 * @param String|null $name
	 * @param String|null $value
	 * @param String|IRenderHTML|null $content
	 * @param String|null $type
	 * @param String|Array|IAttributes $classList attribute instance, class list, or attribute html
	 * @internal param null|String $text
	 * @internal param null|String $value
	 */
	public function __construct($name = null, $value = null, $content = null, $type = null, $classList = null) {
		parent::__construct('button', $classList);
		if ($name)
			$this->setFieldName($name);
		if ($value)
			$this->setInputValue($value);
		if ($type)
			$this->setType($type);
		$this->mContent = $content ?: $value;
	}

	public function getInputValue(IRequest $Request)          { return $this->getAttribute('value'); }
	public function setInputValue($value)    { $this->setAttribute('value', $value); }

	public function getFieldName()           { return $this->getAttribute('name'); }
	public function setFieldName($name)     { $this->setAttribute('name', $name); }

	public function getType()           { return $this->getAttribute('type'); }
	public function setType($value)     { $this->setAttribute('type', $value); }

	public function getFieldID()             { return $this->getAttribute('id'); }
	public function setFieldID($value)       { $this->setAttribute('id', $value); }

	public function setDisabled($disabled=true) {
		if($disabled)
			$this->setAttribute('disabled', 'disabled');
		else
			$this->removeAttribute('disabled');
	}

	public function isDisabled() {
		return $this->hasAttribute('disabled')
			? $this->getAttribute('disabled') === 'disabled'
			: false;
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
		if($this->mContent instanceof IRenderHTML) {
			RI::ai(1);
			$this->mContent->renderHTML($Request);
			RI::ai(-1);
			echo RI::ni();

		} else {
			echo $this->mContent;

		}
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return $this->mContent !== null;
	}
}