<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:11 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Common\RenderableException;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\Validation\FormValidator;
use CPath\Request\Common\IInputField;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Render\HTML\Common\RenderableResponse;
use CPath\Request\Parameter\HTML\ParameterHTMLInputField;
use CPath\Request\Parameter\HTML\HTMLFormItemTemplate;
use CPath\Request\Parameter\IRequestParameter;
use CPath\Request\Validation\IValidation;
use CPath\Response\IResponse;

class HTMLForm extends HTMLElement
{
	const TRIM_CONTENT = false;
    const CSS_FORM_FIELD = 'form-row';

	/**
	 * @param null $method
	 * @param null $action
	 * @param String|Array|IAttributes $classList attribute instance, class list, or attribute html
	 * @param null $_content
	 */
	public function __construct($method = null, $action = null, $classList = null, $_content = null) {
        parent::__construct('form', $classList);
		if($method)
			$this->setMethod($method);
		if($action)
			$this->setAction($action);
		if($_content !== null)
			$this->addAll(array_slice(func_get_args(), 3));
		$this->setItemTemplate(new HTMLLabel());
    }

	public function getRequestValue(IRequest $Request, $paramName=null) {
		$FormValidator = new FormValidator($this);
		return $FormValidator->validateRequest($Request, $paramName);
	}

	public function getFieldName()           { return $this->getAttribute('name'); }
	public function setFieldName($value)     { $this->setAttribute('name', $value); }

	public function getFieldID()             { return $this->getAttribute('id'); }
	public function setFieldID($value)       { $this->setAttribute('id', $value); }

	public function setMethod($method)  { $this->setAttribute('method', $method); }
	public function setAction($action)  { $this->setAttribute('action', $action); }


	public function setFormValues(Array $values, IHTMLContainer $Container=null) {
		if(!$Container)
			$Container = $this;

		foreach($Container->getContent() as $Content) {
			if($Content instanceof IHTMLContainer)
				$values = $this->setFormValues($values, $Content);

			if(!$Content instanceof IHTMLInput)
				continue;

			$name = $Content->getFieldName();
			if(isset($values[$name])) {
				$Content->setInputValue($values[$name]);
				unset($values[$name]);
			}
		}

		return $values;
//		if($values)
//			throw new \InvalidArgumentException("Form fields not found: " . implode(', ', array_keys($values)));
	}

	function addParameter(IRequestParameter $Parameter) {
		$this->addContent(new ParameterHTMLInputField($Parameter));
	}

    public function addSubmit($value = null, $name = null) {
        $this->addInput($value, $name, 'submit');
    }

    public function addInput($value = null, $name = null, $type = null) {
        $Field = new HTMLInputField($name, $value, $type);
        $this->addContent($Field);
    }

    /**
     * Render element content
     * @param IRequest $Request
     * @param IAttributes $ContentAttr
     */
    function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
	    RI::ai(1);
        echo RI::ni(), "<fieldset>";

        parent::renderContent($Request, $ContentAttr);

        echo "</fieldset>";
	    RI::ai(-1);
	    RI::ni();
    }
}

