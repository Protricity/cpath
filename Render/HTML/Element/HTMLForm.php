<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:11 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\Theme\HTMLThemeConfig;
use CPath\Render\HTML\Validation\FormValidator;
use CPath\Request\IRequest;
use CPath\Request\Parameter\IRequestParameter;
use CPath\Request\Parameter\Parameter;
use CPath\Response\IResponse;

class HTMLForm extends HTMLElement implements IResponse
{
	const TRIM_CONTENT = false;
    const CSS_FORM_FIELD = 'form-row';
	const CSS_FORM_SECTION = 'form-section';
	//const CSS_CONTENT_CLASS = 'form-content';


	/**
	 * @param null $method
	 * @param null $action
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param null $_content
	 */
	public function __construct($method = null, $action = null, $classList = null, $_content = null) {
        parent::__construct('form', $classList ?: HTMLThemeConfig::$DefaultFormTheme);
		if($method)
			$this->setMethod($method);
		if($action)
			$this->setAction($action);
		if($_content !== null)
			$this->addAll(array_slice(func_get_args(), 3));
		//$this->setItemTemplate(new HTMLLabel());
    }

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode() { return IResponse::HTTP_SUCCESS; }

	/**
	 * Get the IResponse Message
	 * @return String
	 */
	function getMessage() { return $this->getMethod() . " Form: " . $this->getAction(); }

	public function getContentRecursive(IHTMLContainer $Container=null) {
		return $this->getContainer()
			->getContentRecursive($Container ?: $this);
	}


	public function getRequestValue(IRequest $Request, $paramName=null) {
		$FormValidator = new FormValidator($this);
		return $FormValidator->validateRequest($Request, $paramName);
	}

	public function getFieldName()          { return $this->getAttribute('name'); }
	public function setFieldName($value)    { $this->setAttribute('name', $value); }

	public function getFieldID()            { return $this->getAttribute('id'); }
	public function setFieldID($value)      { $this->setAttribute('id', $value); }

	public function getMethod()             { return $this->getAttribute('method'); }
	public function setMethod($method)      { $this->setAttribute('method', $method); }

	public function getAction()             { return $this->getAttribute('action'); }
	public function setAction($action)      { $this->setAttribute('action', $action); }


	public function setFormValues(Array $values, IHTMLContainer $Container=null) {
		if(!$Container)
			$Container = $this;

		foreach($Container->getContentRecursive() as $Content) {
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
		if($Parameter instanceof IRenderHTML)
			$this->addContent($Parameter);
		else
			$this->addContent(new HTMLInputField($Parameter->getFieldName()));
	}

    public function addSubmit($value = null, $name = null) {
        $this->addInput($value, $name, 'submit');
    }

    public function addInput($value = null, $name = null, $type = null) {
        $Field = new HTMLInputField($name, $value, $type);
        $this->addContent($Field);
    }

//	/**
//	 * Render element content
//	 * @param IRequest $Request
//	 * @param IAttributes $ContentAttr
//	 * @param IRenderHTML $Parent
//	 */
//    function renderContent1(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
//	    RI::ai(1);
//        echo RI::ni(), "<fieldset>";
//
//        parent::renderContent($Request, $ContentAttr);
//
//        echo "</fieldset>";
//	    RI::ai(-1);
//	    RI::ni();
//    }


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
		if ($Content instanceof Parameter)
			$type = $Content->getHTMLInput()->getElementType();
		elseif ($Content instanceof AbstractHTMLElement)
			$type = $Content->getElementType();

		switch($type) {
			case 'textarea':
			case 'input':
				$Render = new HTMLLabel();
				$Render->addContent($Content);
				break;

			case 'fieldset':
			default:
				break;
		}

		$Render->renderHTML($Request, $ContentAttr, $this);
	}
}

