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
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\Validation\Exceptions\ValidationException;
use CPath\Request\Validation\IValidation;
use CPath\Response\IResponse;

class HTMLForm extends HTMLElement implements ILogListener
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
	private $mAction = null;

	/**
	 * @param String $method
	 * @param String $action
	 * @param String $name
	 * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
	 * @param Array|IAttributes|IHTMLSupportHeaders|IRenderHTML|IValidation|null|String $_content [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 */
	public function __construct($method = null, $action = null, $name = null, $classList = null, $_content = null) {
        parent::__construct('form');
		is_scalar($method)      ? $this->setMethod($method)     : $this->addVarArg($method);
		is_scalar($action)      ? $this->setAction($action)     : $this->addVarArg($action);
		is_scalar($name)        ? $this->setFormName($name)     : $this->addVarArg($name);
		is_scalar($classList)   ? $this->addClass($classList)   : $this->addVarArg($classList);

		for($i=4; $i<func_num_args(); $i++)
			$this->addVarArg(func_get_arg($i));
    }

	public function getActionURL(IRequest $Request) {
		$action = $this->mAction;
		if(!$action)
			$action = $Request->getPath();
		$domainPath = $Request->getDomainPath(false);
		if(!$domainPath)
			return $action;
		if(strpos($action, $domainPath) === false)
			return $domainPath . ltrim($action, '/');
		return $action;
	}

	/**
	 * Render html attributes
	 * @param IRequest|null $Request
	 * @internal param bool $return
	 * @return string|void always returns void
	 */
	function renderHTMLAttributes(IRequest $Request=null) {
		if($this->mAction)
			echo ' action="', str_replace('"', "'", $this->getActionURL($Request)), '"';
		parent::renderHTMLAttributes($Request);
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
		foreach($this->getContentRecursive() as $Content)
			if ($Content instanceof IHTMLFormField)
				if ($fieldName === $Content->getFieldName())
					return $Content;
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
		if(!$Container)
			$Container = $this->getContainer(); // $this->mTargetContainer ?:
		$array = array();

		foreach($Container->getContent() as $Content) {
			$array[] = $Content;
			if($Content instanceof IHTMLContainer) {
				foreach($this->getContentRecursive($Content) as $C)
					$array[] = $C;
			}
		}

		return $array;
	}

	public function setFormName($value)    { $this->setAttribute('name', $value); }

//	public function getFieldID()            { return $this->getAttribute('id'); }
//	public function setFieldID($value)      { $this->setAttribute('id', $value); }

	public function getMethod()             { return $this->getAttribute('method'); }
	public function setMethod($method)      { $this->setAttribute('method', $method); }

	public function getAction()             { return $this->getAttribute('action'); }
	public function setAction($action)      { $this->mAction = $action; }

	/**
	 * @param Array|IRequest $values
	 * @param IHTMLContainer $Container
	 */
	public function setFormValues($values, IHTMLContainer $Container=null) {
		if(!$Container)
			$Container = $this;

		if($values instanceof IRequest)
			if(!$this->hasAttribute('action'))
				$this->setAttribute('action', $this->getActionURL($values));

		foreach($Container->getContent() as $Content) {
			if($Content instanceof IHTMLContainer)
				$this->setFormValues($values, $Content);

			if($Content instanceof IHTMLFormField
				&& ($name = $Content->getFieldName())
				&& isset($values[$name])) {
				$Content->setInputValue($values[$name]);
			}
		}
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
				else
					$ex->setForm($this);
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

		if ($Exs) {
			throw new ValidationException($this, $Exs);}

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
		foreach($this->mLogs as $arr) {
			list($msg, $flags) = $arr;
			if(!$this->hasFlag(self::FLAG_SKIP_NEWLINE))
				echo RI::ni();
			echo '<div class="';
			if($flags & self::ERROR)
				echo 'error';
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

		switch(strtolower($type)) {
//			case 'textarea1':
//			case 'input1':
//				$Render = new HTMLLabel();
//				$Render->addContent($Content);
//				break;

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
		if($msg instanceof \Exception)
			$msg = $msg->getMessage();

		if(is_string($msg)) {
			$this->mLogs[$msg] = array($msg, $flags);

		} else {
			$this->mLogs[] = array($msg, $flags);
		}
		$c++;
		return $c;
	}

	public function offsetSet($offset, $value) {
		parent::offsetSet($offset, $value);
	}


}

