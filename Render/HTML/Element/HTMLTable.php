<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/16/14
 * Time: 10:23 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLTable extends HTMLElement
{
	/**
	 * @param null $attributes
	 * @param String|null $_content [optional] varargs of content
	 */
	public function __construct($attributes = null, $_content = null) {
		parent::__construct('table', $attributes);
	}

	/**
	 * Render content item
	 * @param IRequest $Request
	 * @param $index
	 * @param IRenderHTML $Content
	 * @param IAttributes $ContentAttr
	 */
	protected function renderContentItem(IRequest $Request, $index, IRenderHTML $Content, IAttributes $ContentAttr = null) {
		$Render = $Content;

		$type = null;
		if ($Content instanceof AbstractHTMLElement)
			$type = $Content->getElementType();

		switch($type) {
			case 'tbody':
			case 'thead':
			case 'tfoot':
			case 'tr':
				break;

			case 'td':
				$Render = new HTMLElement('tr');
				$Render->addContent($Content);
				break;

			default:
				$Render = new HTMLElement('tr');
				$TD = new HTMLElement('td');
				$Render->addContent($TD);
				$TD->addContent($Content);
				break;
		}

		$Render->renderHTML($Request, $ContentAttr, $this);
	}
}

