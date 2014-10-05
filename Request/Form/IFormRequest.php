<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 12:09 PM
 */
namespace CPath\Request\Form;

use CPath\Request\IRequest;

interface IFormRequest extends IRequest
{
	/**
	 * Return a request value
	 * @param $fieldName
	 * @return mixed|null the form field value or null if not found
	 */
	function getFormFieldValue($fieldName);
}