<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:10 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

class HTMLInputField extends AbstractHTMLElement implements IHTMLInput
{
	const NODE_TYPE = 'input';

	/**
	 * @param null $name
	 * @param null $value
	 * @param null $type
	 * @param String|Array|IAttributes $classList attribute instance, class list, or attribute html
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

    public function getInputValue(IRequest $Request)    { return $this->getAttribute('value'); }
    public function setInputValue($value)               { $this->setAttribute('value', $value); }

	public function getFieldName()                      { return $this->getAttribute('name'); }
	public function setFieldName($name)                 { $this->setAttribute('name', $name); }

	public function getType()                           { return $this->getAttribute('type'); }
	public function setType($value)                     { $this->setAttribute('type', $value); }

    public function getFieldID()                        { return $this->getAttribute('id'); }
    public function setFieldID($value)                  { $this->setAttribute('id', $value); }

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return false;
	}
}