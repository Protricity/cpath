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
			$this->setName($name);
		if ($value)
			$this->setValue($value);
		if ($type)
			$this->setType($type);
		$this->mContent = $content ?: $value;
	}

	public function getValue()          { return $this->getAttribute('value'); }
	public function setValue($value)    { $this->setAttribute('value', $value); }

	public function getName()           { return $this->getAttribute('name'); }
	public function setName($value)     { $this->setAttribute('name', $value); }

	public function getType()           { return $this->getAttribute('type'); }
	public function setType($value)     { $this->setAttribute('type', $value); }

	public function getID()             { return $this->getAttribute('id'); }
	public function setID($value)       { $this->setAttribute('id', $value); }

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	protected function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
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