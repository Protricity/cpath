<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:10 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLInputField extends AbstractHTMLElement implements IHTMLInput
{
	const NODE_TYPE = 'input';

	/**
	 * @param null $name
	 * @param null $value
	 * @param null $type
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 */
    public function __construct($name = null, $value = null, $type = null, $classList = null) {
        parent::__construct(static::NODE_TYPE, $classList);
	    if($value)
		    $this->setInputValue($value);
	    if($name)
		    $this->setFieldName($name);
        if($type)
            $this->setType($type);
    }

    public function getRequestValue(IRequest $Request)    { return $this->getAttribute('value'); }
    public function setInputValue($value)               { $this->setAttribute('value', $value); }

	public function getFieldName()                      { return $this->getAttribute('name'); }
	public function setFieldName($name)                 { $this->setAttribute('name', $name); }

	public function getType()                           { return $this->getAttribute('type'); }
	public function setType($value)                     { $this->setAttribute('type', $value); }

    public function getFieldID()                        { return $this->getAttribute('id'); }
    public function setFieldID($value)                  { $this->setAttribute('id', $value); }


//	/**
//	 * Render request as html
//	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
//	 * @param IAttributes $Attr optional attributes for the input field
//	 * @param IRenderHTML $Parent
//	 * @return String|void always returns void
//	 */
//	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
//		if($Parent instanceof HTMLForm
//			|| $Parent instanceof HTMLFormSection) {
//			$Label = new HTMLLabel();
//			$Label->addContent($this);
//			$Label->renderHTML($Request, $Attr, $Parent);
//			return;
//		}
////		if($Attr) {
////			foreach($Attr->getClasses() as $class) {
////				switch($class) {
////					case HTMLForm::CSS_CONTENT_CLASS:
////						$Label = new HTMLLabel();
////						$Label->addContent($this);
////						$Label->renderHTML($Request, $Attr, $Parent);
////						return;
////				}
////			}
////		}
//		parent::renderHTML($Request, $Attr, $Parent);
//	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
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