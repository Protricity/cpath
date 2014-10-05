<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/4/14
 * Time: 9:05 AM
 */
namespace CPath\Request\Form;

use CPath\Request\AbstractRequestWrapper;

class FormRequestWrapper extends AbstractRequestWrapper implements IFormRequest
{

	private $mFormData;

	public function __construct(Array $formData) {
		$this->mFormData = $formData;
	}

	/**
	 * Return a request value
	 * @param $fieldName
	 * @return mixed the form field value
	 */
	function getFormFieldValue($fieldName) {
		if (!empty($this->mFormData[$fieldName]))
			return $this->mFormData[$fieldName];

		return null;
	}
}