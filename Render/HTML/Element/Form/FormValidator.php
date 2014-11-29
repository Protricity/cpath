<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 6:58 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Render\HTML\Common\RenderableException;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\Validation\Exceptions\ValidationException;
use CPath\Request\Validation\IValidation;
use CPath\Response\IResponse;

class FormValidator implements IResponse, ILogListener
{
	/** @var \CPath\Render\HTML\Element\Form\HTMLForm */
	private $mForm;
	/** @var \Exception */
	private $mLastException = null;
	/** @var ILogListener[] */
	private $mLogListeners = array();
	/** @var IValidation[] */
	private $mValidations = array();

	public function __construct(HTMLForm $Form) {
		$this->mForm = $Form;
	}

	function addFieldValidation(IValidation $Validation, $fieldName) {
		$this->mValidations[] = array($Validation, $fieldName);
		return $this;
	}

	function getFormField($fieldName) {
		return $this->mForm->getFormField($fieldName);
	}

	public function validateField(IRequest $Request, $fieldName) {
		$Field = $this->getFormField($fieldName);
		$value = $Field->getRequestValue($Request);

		if ($Field instanceof IValidation)
			try {
				$value = $Field->validate($Request, $value, $fieldName);

			} catch (\Exception $ex) {
				if(!$ex instanceof RequestException)
					$ex = new RequestException($ex->getMessage(), $ex->getCode() ?: IResponse::HTTP_ERROR, $ex);

				$Form = $this->mForm;
				$ex->setRenderable($Form);
				$Form->log($ex, $Form::ERROR);
				throw $ex;
			}

		return $value;
	}

	/**
	 * Validate a form request and returns the values
	 * @param IRequest $Request
	 * @throws \CPath\Render\HTML\Common\RenderableException
	 * @return array|string
	 */
	function validateRequest(IRequest $Request) {
		$Form   = $this->mForm;
		$values = array();
		/** @var RequestException[] $Exs */
		$Exs   = array();
		$c     = 0;
		$Validations = $this->mValidations;
		foreach ($Form->getContentRecursive() as $Content) {
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
			throw $this->mLastException = new ValidationException($this->mForm, $Exs);

		$this->mLastException = null;

		$this->log("Validation completed successfully");

		return $values;
	}

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode() {
		return $this->mLastException
			? ($this->mLastException->getCode() ?: IResponse::HTTP_ERROR)
			: IResponse::HTTP_SUCCESS;
	}

	/**
	 * Get the IResponse Message
	 * @return String
	 */
	function getMessage() {
		return $this->mLastException
			? $this->mLastException->getMessage()
			: "Form validation successful";
	}

	/**
	 * Add a log entry
	 * @param mixed $msg The log message
	 * @param int $flags [optional] log flags
	 * @return int the number of listeners that processed the log entry
	 */
	function log($msg, $flags = 0) {
		foreach($this->mLogListeners as $Log)
			$Log->log($msg, $flags);
	}

	/**
	 * Add a log listener callback
	 * @param ILogListener $Listener
	 * @return void
	 */
	function addLogListener(ILogListener $Listener) {
		$this->mLogListeners[] = $Listener;
	}
}