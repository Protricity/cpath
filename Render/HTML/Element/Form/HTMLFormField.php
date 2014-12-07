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
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IHTMLContainerItem;
use CPath\Render\HTML\IHTMLElement;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\Validation\Exceptions\ValidationException;
use CPath\Request\Validation\IValidation;
use CPath\Request\Validation\ValidationCallback;

class HTMLFormField extends AbstractHTMLElement implements IHTMLFormField, IValidation, ILogListener, IHTMLContainerItem
{
	const NODE_TYPE = 'input';
	const INPUT_TYPE = null;

	/** @var IValidation[]  */
	private $mValidations = array();

	private $mLogs = array();

	/**
	 * @param String|null $classList a list of class elements
	 * @param String|null $name field name (name=[])
	 * @param String|null $value input value (value=[])
	 * @param String|null $type input type (type=[])
	 * @param String|null|Array|IAttributes|IHTMLSupportHeaders|IValidation $_validation [varargs] attribute html as string, array, or IValidation || IAttributes instance
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

	protected function addVarArg($arg, $allowHTMLAttributeString=false) {
		if($arg instanceof IValidation)
			$this->addValidation($arg);
		else if($arg instanceof \Closure)
			$this->addValidation(new ValidationCallback($arg));
		else
			parent::addVarArg($arg, $allowHTMLAttributeString);
	}

	/**
	 * Get the request value from the IRequest
	 * @param IRequest $Request
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
			if($Parent instanceof IHTMLElement)
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
		foreach($this->mLogs as $msg => $flags) {
			echo RI::ni(), '<div class="';
			if($flags & self::ERROR)
				echo 'error';
			echo '">', $msg, '</div>';
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
				$Validation->validate($Request, $value, $fieldName);
			} catch (\Exception $ex) {
				$Exs[] = $ex;
				$this->log($ex->getMessage(), $this::ERROR);
			}
		}

		if ($Exs) {
			$Form = $this->getForm();
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