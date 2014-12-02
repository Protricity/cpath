<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:10 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\AbstractHTMLElement;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\Validation\Exceptions\ValidationException;
use CPath\Request\Validation\IValidation;

class HTMLFormField extends AbstractHTMLElement implements IHTMLFormField, IValidation, ILogListener
{
	const NODE_TYPE = 'input';
	const INPUT_TYPE = null;

	/** @var IValidation[]  */
	private $mValidations = array();

	/** @var HTMLForm */
	private $mForm = null;
	private $mLogs = array();

	/**
	 * @param String|null $classList a list of class elements
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @param String|null $type input type (type=[])
	 * @param String|null|Array|IAttributes|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 */
    public function __construct($classList = null, $name = null, $value = null, $type = null, $_validation = null) {
        parent::__construct(static::NODE_TYPE);
	    if(is_string($classList))
		    $this->addClass($classList);
	    if(is_string($name))
		    $this->setFieldName($name);
	    if($type === null)
		    $type = static::INPUT_TYPE;
        if(is_string($type))
            $this->setType($type);
	    if(is_string($value))
		    $this->setInputValue($value);

	    foreach(func_get_args() as $i => $arg)
		    $this->addVarArg($arg, $i>=4);
    }

	protected function addVarArg($arg, $allowHTMLString=false) {
		$added = parent::addVarArg($arg, $allowHTMLString);
		if($arg instanceof IValidation) {
			$this->addValidation($arg);
			$added = true;
		}

		return $added;
	}

	/**
	 * Get the request value from the IRequest
	 * @param IRequest $Request
	 * @throws RequestException if the parameter failed validated
	 * @return mixed
	 */
	public function getRequestValue(IRequest $Request) {
		$fieldName = $this->getFieldName();
		if($this->getForm()) {
			switch(strtoupper($this->getForm()->getMethod())) {
				case 'POST':
					if($Request instanceof IFormRequest)
						return $Request->getFormFieldValue($fieldName);
			}
		}
		return $Request[$fieldName];
	}

	public function getInputValue()                     { return $this->getAttribute('value'); }
	public function setInputValue($value)               { $this->setAttribute('value', $value); }

	public function getFieldName()                      { return $this->getAttribute('name'); }
	public function setFieldName($name)                 { $this->setAttribute('name', $name); }

	public function getType()                           { return $this->getAttribute('type'); }
	public function setType($value)                     { $this->setAttribute('type', $value); }

	public function setDisabled($disabled=true) {
		if($disabled)   $this->setAttribute('disabled', 'disabled');
		else            $this->removeAttribute('disabled');
	}

	/**
	 * @param HTMLForm $Form
	 */
	function setForm(HTMLForm $Form) {
		$this->mForm = $Form;
	}

	/**
	 * Return the form field's form instance or null
	 * @return HTMLForm|null
	 */
	function getForm() {
		return $this->mForm;
	}

	/**
	 * Render HTML Form Field
	 * @param IRequest $Request
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		foreach($this->mLogs as $msg => $flags) {
			echo RI::ni(), '<div';
			if($flags & self::ERROR)
				echo ' class="error"';
			echo '>', $msg, '</div>';
		}

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
		foreach(func_get_args() as $Validation)
			$this->mValidations[] = $Validation;
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
				$Validation->validate($Request, $value, $fieldName);
			} catch (\Exception $ex) {
				$Exs[] = $ex;
				$this->log($ex->getMessage(), $this::ERROR);
			}
		}

		if ($Exs) {
			$Form = $this->mForm;
			if(!$Form) {
				$Form = new HTMLForm();
				$Form->addContent($this);
				$Form->addContent(new HTMLSubmit());
			}
			throw new ValidationException($Form, $Exs);
		}

		if($value && $this->getInputValue() === null)
			$this->setInputValue($value);

		return $value;
	}

	function __toString() {
		return basename(get_class($this)) . ': ' . $this->getFieldName();
	}

	function __invoke(IRequest $Request) {
		$value = $this->validate($Request);
		return $value;
	}

	// Static
//
//	/**
//	 * @param null $description
//	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
//	 * @param null $name
//	 * @param null $checked
//	 * @param null $type
//	 * @return \CPath\Render\HTML\Element\Form\HTMLFormField
//	 */
//	static function get($description = null, $classList = null, $name = null, $checked = null, $type = null) {
//		return new HTMLFormField($description, $classList, $name, $checked, $type);
//	}
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
//		if($this->mForm)
//			$c += $this->mForm->log($msg, $flags);

		$this->mLogs[$msg] = $flags;
		$c++;
		return $c;
	}
}