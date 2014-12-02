<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 10:36 AM
 */
namespace CPath\Render\HTML\Element;

use CPath\Data\Describable\IDescribable;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\Form\IHTMLFormField;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\Validation\IValidation;

class HTMLLabel extends HTMLElement
{
	//const PASS_DOWN_ATTRIBUTES = true;
	private $mText = null;

	/**
	 * @param string $text
	 * @param String|\CPath\Render\HTML\Attribute\IAttributes $_attributes
	 * @param null $_content
	 */
    public function __construct($text=null, $_attributes=null, $_content=null) {
        parent::__construct('label', $_attributes);
	    $this->mText = $text;

	    if($_content !== null)
		    for($i=2;$i<func_num_args();$i++)
			    $this->addAll(func_get_arg($i));
    }

	/**
	 * Add HTML Container MainContent
	 * @param \CPath\Render\HTML\IRenderHTML $Content
	 * @param null $key
	 * @return void
	 */
	function addContent(IRenderHTML $Content, $key = null) {
		if ($Content instanceof IHTMLFormField)
			$this->setAttribute('for', $Content->getFieldName());

		parent::addContent($Content, $key);
	}

	protected function renderContentItem(IRequest $Request, $index, IRenderHTML $Content, IAttributes $ContentAttr = null) {
		if ($Content instanceof IDescribable)
			echo  RI::ni(), '<span>' . $Content->getDescription() . '</span>';

		if ($Content instanceof IValidation) {
			try {
				$Content->validate($Request);
			} catch (\Exception $ex) {
				echo  RI::ni(), '<span class="error">' . $ex->getMessage() . '</span>';
			}
		}

		parent::renderContentItem($Request, $index, $Content, $ContentAttr);
	}

	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		if($this->mText)
			echo  RI::ni(), '<span>' . $this->mText . '</span>';

		parent::renderContent($Request, $ContentAttr, $Parent);
	}

}


