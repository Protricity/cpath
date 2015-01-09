<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 10:28 AM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\AbstractHTMLElement;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLSelectOptionElement extends AbstractHTMLElement
{
	const TRIM_CONTENT     = true;

	private $mDescription;

	public function __construct($value, $description = null, $selected=false, $classList = null) {
		parent::__construct('option', $classList);
		$this->mDescription = $description;
			$this->setAttribute('value', $value);
		if ($selected)
			$this->setAttribute('selected', 'selected');
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		echo $this->mDescription ?: $this->getAttribute('value');
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return true;
	}
}