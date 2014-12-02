<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/27/14
 * Time: 5:11 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\AbstractHTMLElement;
use CPath\Render\HTML\Element\HTMLElement;
use CPath\Render\HTML\Element\HTMLLabel;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\Theme\HTMLThemeConfig;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\Validation\Exceptions\ValidationException;
use CPath\Request\Validation\IValidation;
use CPath\Response\IResponse;

class HTMLForm extends HTMLElement implements IResponse, ILogListener
{
	const TRIM_CONTENT = false;
    const CSS_FORM_FIELD = 'form-row';
	const CSS_FORM_SECTION = 'form-section';
	//const CSS_CONTENT_CLASS = 'form-content';

	/** @var IResponse */
	private $mFormValidation =  null;
	private $mLogs = array();

	/** @var IValidation[]  */
	private $mValidations = array();

	/**
	 * @param null $method
	 * @param null $action
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param null $_validation
	 */
	public function __construct($method = null, $action = null, $classList = null, $_validation = null) {
        parent::__construct('form', $classList ?: HTMLThemeConfig::$DefaultFormTheme);
		if($method)
			$this->setMethod($method);
		if($action)
			$this->setAction($action);
		if($_validation !== null)
			$this->addAll(array_slice(func_get_args(), 3));
		//$this->setItemTemplate(new HTMLLabel());
    }

	function addFieldValidation(IValidation $Validation, $fieldName) {
		$this->mValidations[] = array($Validation, $fieldName);
		return $this;
	}


	/**
	 * @param $fieldName
	 * @throws \InvalidArgumentException
	 * @return IHTMLFormField
	 */
	function getFormField($fieldName) {
		foreach($this->getContentRecursive() as $Content) {
			if($Content instanceof IHTMLFormField)
				if($Content->getFieldName() === $fieldName)
					return $Content;
		}
		throw new \InvalidArgumentException("Form field not found in form: " . $fieldName);
	}

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode() {
		return $this->mFormValidation ? $this->mFormValidation->getCode() : IResponse::HTTP_SUCCESS;
	}

	/**
	 * Get the IResponse Message
	 * @return String
	 */
	function getMessage() {
		return $this->mFormValidation ? $this->mFormValidation->getMessage() : $this->getMethod() . " Form: " . $this->getAction();
	}


	public function getContentRecursive(IHTMLContainer $Container=null) {
		$Content = $this->getContainer()
			->getContentRecursive($Container ?: $this);
		foreach($Content as $ContentItem)
			if($ContentItem instanceof IHTMLFormField)
				if(!$ContentItem->getForm())
					$ContentItem->setForm($this);
		return $Content;
	}

	public function getFieldName()          { return $this->getAttribute('name'); }
	public function setFieldName($value)    { $this->setAttribute('name', $value); }

//	public function getFieldID()            { return $this->getAttribute('id'); }
//	public function setFieldID($value)      { $this->setAttribute('id', $value); }

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

			if(!$Content instanceof IHTMLFormField)
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

//    public function addSubmit($classList = null, $value = null, $name = null) {
//	    $Field = new HTMLSubmit($classList, $value, $name);
//	    $this->addContent($Field);
//    }
//
//    public function addInput($classList = null, $value = null, $name = null, $type = null) {
//        $Field = new HTMLFormField($classList, $value, $name, $type);
//        $this->addContent($Field);
//    }

	function addContent(IRenderHTML $Render, $key = null) {
		if($Render instanceof IHTMLContainer) {
			foreach($Render->getContent() as $Content) {
				if($Content instanceof IHTMLFormField) {
					$Content->setForm($this);
				}
			}
		}

		if($Render instanceof IHTMLFormField)
			$Render->setForm($this);

		parent::addContent($Render, $key);
	}

	public function validateField(IRequest $Request, $fieldName) {
		$Field = $this->getFormField($fieldName);
		$value = $Field->getRequestValue($Request);

		if ($Field instanceof IValidation)
			try {
				$value = $Field->validate($Request, $value, $fieldName);


			} catch (\Exception $ex) {
				if(!$ex instanceof ValidationException)
					$ex = new ValidationException($this, $ex);
				$this->log($ex->getMessage(), $this::ERROR);
				throw $ex;
			}

		return $value;
	}

	/**
	 * Validate a form request and returns the values
	 * @param IRequest $Request
	 * @throws ValidationException
	 * @return array|string
	 */
	function validateRequest(IRequest $Request) {
		$values = array();
		/** @var RequestException[] $Exs */
		$Exs   = array();
		$c     = 0;
		$Validations = $this->mValidations;
		foreach ($this->getContentRecursive() as $Content) {
			if (!$Content instanceof IHTMLFormField)
				continue;

			$name = $Content->getFieldName();

			if ($Content instanceof IValidation) {
				$Validations[] = array($Content, $name);

			} else {
				$values[$Content->getFieldName()] = $Content->getRequestValue($Request);
			}
		}

		foreach($Validations as $arr) {
			list($Validation, $fieldName) = $arr;
			if ($Validation instanceof IHTMLFormField)
				$value = $Validation->getRequestValue($Request);
			else
				$value = $this->getFormField($fieldName)
					->getRequestValue($Request);

			/** @var IValidation $Validation */
			try {
				$return = $Validation->validate($Request, $value, $fieldName);
				$values[$fieldName] = $return;
				$c++;

			} catch (\Exception $ex) {
				$this->log($ex, static::ERROR);
				$Exs[]         = $ex;
				$values[$fieldName] = null;
			}
		}

		if ($Exs)
			throw new ValidationException($this, $Exs);

		$this->log("Form Validation completed successfully");

		return $values;
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
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		foreach($this->mLogs as $msg => $flags) {
			echo RI::ni(), '<div class="';
			if($flags & self::ERROR)
				echo ' error';
			echo '">', $msg, '</div>';
		}
		parent::renderHTML($Request, $Attr, $Parent);
	}


	/**
	 * Render content item
	 * @param IRequest $Request
	 * @param $index
		* @param IRenderHTML $Content
		* @param IAttributes $ContentAttr
		*/
	protected function renderContentItem(IRequest $Request, $index, IRenderHTML $Content, IAttributes $ContentAttr = null) {
		$type = null;
		if ($Content instanceof AbstractHTMLElement)
			$type = $Content->getElementType();

		if ($Content instanceof IHTMLFormField)
			if(!$Content->getForm())
				$Content->setForm($this);

		switch(strtolower($type)) {
			case 'textarea':
			case 'input':
				$Render = new HTMLLabel();
				$Render->addContent($Content);
				break;

			case 'fieldset':
			default:
				$Render = $Content;
				break;
		}

		$Render->renderHTML($Request, $ContentAttr, $this);
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
//		foreach($this->getContentRecursive() as $Content)
//			if($Content instanceof ILogListener)
//				$c += $Content->log($msg, $flags);

		$this->mLogs[$msg] = $flags;
		$c++;
		return $c;
	}

	public function offsetSet($offset, $value) {
		if (!$value instanceof IRenderHTML) {
		}
		parent::offsetSet($offset, $value);
	}


}

