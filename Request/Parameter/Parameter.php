<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 2:06 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Attribute\AttributeCollection;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\Form\HTMLForm;
use CPath\Render\HTML\Element\Form\HTMLFormField;
use CPath\Render\HTML\Element\Form\IHTMLFormField;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Validation\Exceptions\ValidationException;
use CPath\Request\Validation\IValidation;

class Parameter implements IRequestParameter, IValidation
{
	const CSS_CLASS_ERROR = 'error';

    private $mName, $mDescription;
	private $mPlaceholder = null;
	private $mAttributes = array();
	private $mValue = null;
	private $mLastException;
	private $mForm = null;
	protected $mValidations = array();

    public function __construct($paramName, $description=null, $defaultValue=null) {
	    $this->mName = $paramName;
	    $this->mDescription = $description;
	    $this->mValue = $defaultValue;
    }

	function addAttributes(IAttributes $Attributes) {
		$this->mAttributes[] = $Attributes;
	}

	public function getInputValue()                     { return $this->mValue; }
	public function setInputValue($value)               { $this->mValue = $value; }

    function getFieldName() {
        return $this->mName;
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
	 * Get parameter description
	 * @return String
	 */
	function getDescription() {
		return $this->mDescription;
	}

	function setDescription($text) {
		$this->mDescription = $text;
		return $this;
	}

	function setPlaceholder($text) {
		$this->mPlaceholder = $text;
		return $this;
	}

	function addFilter($filter, $options=null, $description=null) {
		$this->mValidations[] = func_get_args();
	}

	function addValidation(IValidation $Validation) {
		$this->mValidations[] = $Validation;
	}

	/**
	 * Get the request value
	 * @param \CPath\Request\IRequest $Request
	 * @throws RequestException if the parameter failed validated
	 * @return mixed
	 */
	function getRequestValue(IRequest $Request) {
		$name = $this->getFieldName();
		if(isset($Request[$name])) {
			$this->mValue = $Request[$name];
		} else {
			$this->mValue = null;
		}
		return $this->mValue;
	}

	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Validation\Exceptions\ValidationException
	 * @throw Exception if validation failed
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value=null, $fieldName = null) {
		$value = $value ?: $this->getRequestValue($Request);
		/** @var \Exception[] $Exs */
		$Exs = array();

		$fieldName = $fieldName ?: $this->getFieldName();
		foreach($this->mValidations as $Validation) {
			try {
				if($Validation instanceof IValidation) {
					$Validation->validate($Request, $value, $fieldName);

				} else {
					list($filterID, $filterOpts, $desc) = $Validation + array(-1, null, null);
					$value = filter_var($value, $filterID, $filterOpts);
					if($value === false && $filterID !== FILTER_VALIDATE_BOOLEAN) {
						if(!$desc) {
							foreach(filter_list() as $name) {
								if(filter_id($name) === $filterID) {
									$desc = "Filter failed: " . $name;
									break;
								}
							}
							if(!$desc)
								$desc = "Filter failed: " . implode(', ', $Validation);
						}
						throw new RequestException($desc);
					}

				}
			} catch (\Exception $ex) {
				$Exs[] = $ex;
			}
		}

		if ($Exs)
			throw $this->mLastException = new ValidationException($this->mForm, $Exs);

		return $value;
	}


	function getHTMLInput(IHTMLFormField $Input=null) {
		$Input = $Input ?: new HTMLFormField(, $this->getFieldName());
		if($this->mValue)
			$Input->setInputValue($this->mValue);
		if($this->mPlaceholder)
			$Input->setAttribute('placeholder', $this->mPlaceholder);
		//$Input->setAttribute('placeholder', $this->getDescription()); // $this->mPlaceholder ?: $this->getFieldName());
		$Input->addValidation($this);
		return $Input;
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
    function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
	    $Input = $this->getHTMLInput();
	    $Attrs = $this->mAttributes;
	    if($Attr) {
		    $Attrs[] = $Attr;
		    if(sizeof($Attrs) > 1)
			    $Attr = AttributeCollection::combineA($Attrs);
	    } else {
		    if(sizeof($Attrs) > 0)
			    $Attr = AttributeCollection::combineA($Attrs);
	    }

	    if(!$this->mLastException && $Request instanceof IFormRequest)
		    try {
			    $this->validate($Request);
		    } catch (\Exception $ex) {

		    }

	    if($this->mLastException)
		    $Input->addClass(self::CSS_CLASS_ERROR);

	    $Input->renderHTML($Request, $Attr, $Parent);
	}

	function __invoke(IRequest $Request) {
		$value = $this->getRequestValue($Request);
		$value = $this->validate($Request, $value, $this->getFieldName());
		return $value;
	}

	// Static

//	static function validate(IRequest $Request) {
//		$Params = new SessionParameters($Request);
//		$Params->validateRequest($Request);
//	}

//	static function tryStatic(IRequest $Request, $paramName, $paramDescription=null, $defaultValue=null) {
//		$Parameter = new Parameter($paramName, $paramDescription, $defaultValue);
//		$Parameter->tryValue($Request, $Exceptions);
//		if($Exceptions instanceof \Exception)
//			throw $Exceptions;
//	}
}

