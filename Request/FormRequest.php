<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/22/14
 * Time: 2:14 PM
 */
namespace CPath\Request;

use CPath\Request\Form\IFormRequest;
use CPath\Request\MimeType\IRequestedMimeType;

class FormRequest extends Request implements IFormRequest
{
	public function __construct($method, $path, $parameters = array(), IRequestedMimeType $MimeType = null) {
		parent::__construct($method, $path, $parameters, $MimeType);
	}

	/**
	 * Return a request value
	 * @param $fieldName
	 * @return mixed|null the form field value or null if not found
	 */
	function getFormFieldValue($fieldName) {
		return parent::getRequestValue($fieldName);
	}
}