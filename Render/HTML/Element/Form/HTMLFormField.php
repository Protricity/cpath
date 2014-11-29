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
use CPath\Request\Validation\Exceptions\ValidationException;
use CPath\Request\Validation\IValidation;

class HTMLFormField extends AbstractHTMLElement implements IHTMLFormField, IValidation
{
	const NODE_TYPE = 'input';
	const INPUT_TYPE = null;

	/** @var IValidation[]  */
	private $mValidations = array();
	/** @var \Exception */
	private $mLastException = null;

	/** @var HTMLForm */
	private $mForm = null;

	/**
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param null $name
	 * @param null $value
	 * @param null $type
	 */
    public function __construct($classList = null, $name = null, $value = null, $type = null) {
        parent::__construct(static::NODE_TYPE, $classList);
	    if($name)
		    $this->setFieldName($name);
        if($type || static::INPUT_TYPE)
            $this->setType($type ?: static::INPUT_TYPE);
	    if($value)
		    $this->setInputValue($value);
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
		if($this->mLastException)
			echo RI::ni(), '<div class="error">', $this->mLastException->getMessage(), '</div>';

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

	/**
	 * Add input validation to this form field
	 * @param IValidation $Validation
	 * @return $this
	 */
	function addValidation(IValidation $Validation) {
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
		$Exs = array();

		$fieldName = $this->getFieldName();
		foreach($this->mValidations as $Validation) {
			try {
				$Validation->validate($Request, $value, $fieldName);
			} catch (\Exception $ex) {
				$Exs[] = $ex;
			}
		}

		if ($Exs)
			throw $this->mLastException = new ValidationException($this->mForm, $Exs);

		if($value && $this->getInputValue() === null)
			$this->setInputValue($value);

		return $value;
	}

	function __toString() {
		return $this->getDescription() !== null
			? $this->getDescription()
			: basename(get_class($this)) . ': ' . $this->getFieldName();
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
}