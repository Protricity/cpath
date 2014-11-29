<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/19/14
 * Time: 10:56 AM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class ClosedHTMLElement extends AbstractHTMLElement
{
	/**
	 * @param string $elmType
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 */
	public function __construct($elmType, $classList = null) {
		parent::__construct($elmType, $classList);
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param IRenderHTML $Parent
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return false;
	}
}