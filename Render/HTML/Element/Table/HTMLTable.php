<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/16/14
 * Time: 10:23 PM
 */
namespace CPath\Render\HTML\Element\Table;

use CPath\Data\Map\ISequenceMap;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\AbstractHTMLElement;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Element\Table;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLTable extends HTMLElement
{
	/**
	 * @param null $classList
	 * @param String|null $_content [optional] varargs of content
	 * @internal param null $attributes
	 */
	public function __construct($classList = null, $_content = null) {
		parent::__construct('table');

		is_scalar($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=1; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
	}

	protected function addVarArg($arg) {
		if($arg instanceof ISequenceMap)
			$arg = new HTMLTableSequenceRenderer($arg);

		parent::addVarArg($arg);
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
//				$Render = new HTMLElement('tr');
//				$TD = new HTMLElement('td');
//				$Render->addContent($TD);
//				$TD->addContent($Content);
				$this->mapNext($Content);
				break;
		}

		$Render->renderHTML($Request, $ContentAttr, $this);
	}
}

