<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 5:39 PM
 */
namespace CPath\Render\HTML\Element\Form;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\Validation\IValidation;

interface IHTMLFormField
{
	/**
	 * Return the form field's form instance or null
	 * @return HTMLForm|null
	 */
	function getForm();

	/**
	 * Get the request value from the IRequest
	 * @param IRequest $Request
	 * @throws RequestException if the parameter failed validated
	 * @return mixed
	 */
	public function getRequestValue(IRequest $Request);

	/**
	 * Get parameter name
	 * @return String
	 */
	public function getFieldName();

	/**
	 * Set input value
	 * @param $value
	 * @return mixed
	 */
	public function setInputValue($value);

	/**
	 * Add input validation to this form field
	 * @param IValidation $Validation
	 */
	//public function addValidation(IValidation $Validation);


	/**
	 * Get the field value
	 * @return mixed
	 */
	//public function getInputValue();
}