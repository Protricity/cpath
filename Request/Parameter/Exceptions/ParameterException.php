<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/18/14
 * Time: 12:57 PM
 */
namespace CPath\Request\Parameter\Exceptions;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\Parameter\IRequestParameter;

class ParameterException extends RequestException
{
	private $mParameter;

	public function __construct(IRequestParameter $Parameter, $message = null) {
		$this->mParameter = $Parameter;
		parent::__construct($message ? : "Parameter failed validation: " . $Parameter->getFieldName());
		$this->setRenderable($this->getForm());
	}

	public function getForm() {
		return $this->mParameter->getForm();
	}
}