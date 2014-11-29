<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/28/14
 * Time: 1:32 AM
 */
namespace CPath\Request\Validation\Exceptions;

use CPath\Render\HTML\Element\Form\HTMLForm;
use CPath\Request\Exceptions\RequestException;
use CPath\Response\IResponse;
use Exception;

class ValidationException extends RequestException
{
	private $mForm;
	private $mExceptions;

	/**
	 * @param HTMLForm $Form
	 * @param Exception|Exception[] $Exceptions
	 * @internal param string $message
	 */
	public function __construct(HTMLForm $Form, $Exceptions = null) {
		$this->mForm = $Form;

		if (!is_array($Exceptions))
			$Exceptions = array($Exceptions);

		$message = sizeof($Exceptions) . " Exception(s) occurred during validation:";

		foreach($Exceptions as $Ex)
			$message .= "\n\t" . ($Ex instanceof Exception ? $Ex->getMessage() : $Ex);

		$this->mExceptions = $Exceptions;

		parent::__construct($message, IResponse::HTTP_FORBIDDEN, $Exceptions[0]);

		$this->setRenderable($Form);
	}

	function getForm() {
		return $this->getForm();
	}

	function getExceptions() {
		return $this->mExceptions;
	}
}