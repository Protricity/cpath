<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:10 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\AbstractHTMLElement;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IHTMLContainerItem;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\Validation\Exceptions\ValidationException;
use CPath\Request\Validation\IValidation;
use CPath\Request\Validation\ValidationCallback;

class HTMLInputField extends AbstractHTMLElement implements IHTMLFormField, IValidation, ILogListener
{
	const NODE_TYPE = 'input';
	const INPUT_TYPE = null;

	/** @var IValidation[]  */
	private $mValidations = array();

	private $mLogs = array();

	/**
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @param String|null $type input type (type=[])
	 * @param String|null $classList a list of element classes
	 * @param String|null|Array|IAttributes|IHTMLSupportHeaders|IValidation $_content [varargs] class as string, array, or IValidation || IAttributes instance
	 * @internal param null|String $classList a list of class elements
	 */
    public function __construct($name = null, $value = null, $type = null, $classList=null, $_content = null) {
        parent::__construct(static::NODE_TYPE);

	    if(static::INPUT_TYPE)
		    $this->setType(static::INPUT_TYPE);
	    is_scalar($name)        ? $this->setFieldName($name)    : $this->addVarArg($name);
	    is_scalar($value)       ? $this->setInputValue($value)  : $this->addVarArg($value);
	    is_scalar($type)        ? $this->setType($type)         : $this->addVarArg($type);
	    is_scalar($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

	    for($i=4; $i<func_num_args(); $i++)
		    $this->addVarArg(func_get_arg($i));
    }

	protected function addVarArg($arg, $allowHTMLAttributeString=false) {
		if($arg instanceof IValidation)
			$this->addValidation($arg);
		else if($arg instanceof \Closure)
			$this->addValidation(new ValidationCallback($arg));

		parent::addVarArg($arg, $allowHTMLAttributeString);
	}

	/**
	 * Get the request value from the IRequest
	 * @param IRequest $Request
	 * @return mixed|null
	 */
	public function getRequestValue(IRequest $Request) {
		$fieldName = $this->getFieldName();
		if($this->getForm()) {
			switch(strtoupper($this->getForm()->getMethod())) {
				case 'POST':
					if($Request instanceof IFormRequest)
						return $Request->getFormFieldValue($fieldName);
					return null;
			}
		}
		return isset($Request[$fieldName]) ? $Request[$fieldName] : null;
	}

	public function getInputValue()                     { return $this->getAttribute('value'); }
	public function setInputValue($value)               { $this->setAttribute('value', $value); }

	public function getFieldName()                      { return $this->getAttribute('name'); }
	public function setFieldName($name)                 { $this->setAttribute('name', $name); }

	public function getType()                           { return $this->getAttribute('type'); }
	public function setType($value)                     { $this->setAttribute('type', $value); }

	public function setPlaceholder($value)              { $this->setAttribute('placeholder', $value); }

	public function setDisabled($disabled=true) {
		if($disabled)   $this->setAttribute('disabled', 'disabled');
		else            $this->removeAttribute('disabled');
	}

	/**
	 * Return the form field's form instance or null
	 * @return HTMLForm|null
	 */
	function getForm() {
		$Parent = $this->getParent();
		while($Parent) {
			if($Parent instanceof HTMLForm)
				return $Parent;
			if($Parent instanceof IHTMLContainerItem)
				$Parent = $Parent->getParent();
			else
				return null;
		}
		return null;
	}

	/**
	 * Render HTML Form Field
	 * @param IRequest $Request
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
//		foreach($this->mLogs as $msg => $flags) {
//			echo RI::ni(), '<div class="';
//			if($flags & self::ERROR)
//				echo 'error';
//			echo '">', $msg, '</div>';
//		}

		parent::renderHTML($Request, $Attr, $Parent);
	}

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

	function addValidation(IValidation $Validation, IValidation $_Validation=null) {
		foreach(func_get_args() as $Validation) {
			$this->mValidations[] = $Validation;
			if($Validation instanceof IAttributes)
				$this->addAttributes($Validation);
		}
		return $this;
	}

	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Validation\Exceptions\ValidationException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value = null, $fieldName = null) {
		$value = $value ?: $this->getRequestValue($Request);
		$fieldName = $fieldName ?: $this->getFieldName();

		$Exs = array();

		foreach($this->mValidations as $Validation) {
			try {
				$newValue = $Validation->validate($Request, $value, $fieldName);
				if($newValue !== null)
					$value = $newValue;
			} catch (\Exception $ex) {
				$Exs[] = $ex;
				$this->log($ex->getMessage(), $this::ERROR);
			}
		}

		if ($Exs) {
			$Form = $this->getForm();
			if(!$Form) {
				$Form = new HTMLForm($Request->getMethodName(), $Request->getPath(),
					new HTMLElement('legend', 'content-title', $Request->getPath()),

					"Enter value for field '" . $this->getFieldName() . "': ",
					$this,
					new HTMLSubmit()

					);
			}
			throw new ValidationException($Form, $Exs);
		}

		if($value && $this->getInputValue() === null)
			$this->setInputValue($value);

		return $value;
	}

	/**
	 * Add a log entry
	 * @param mixed $msg The log message
	 * @param int $flags [optional] log flags
	 * @return int the number of listeners that processed the log entry
	 */
	function log($msg, $flags = 0) {
		$c = 0;
		foreach($this->getLogListeners() as $Log)
			$c += $Log->log($msg, $flags);

		$this->mLogs[$msg] = $flags;
		$c++;
		return $c;
	}

	function __toString() {
		return basename(get_class($this)) . ': ' . $this->getFieldName();
	}

	function __invoke(IRequest $Request) {
		$value = $this->validate($Request);
		return $value;
	}
}