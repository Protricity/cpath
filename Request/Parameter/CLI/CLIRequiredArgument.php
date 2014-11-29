<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/6/14
 * Time: 6:52 PM
 */
namespace CPath\Request\Parameter\CLI;

use CPath\Request\IRequest;
use CPath\Request\Parameter\Exceptions\RequiredParameterException;

class CLIRequiredArgument extends CLIArgument
{
	public function __construct($index, $optionName, $description=null, $defaultValue=null) {
		parent::__construct($index, $optionName, $description, $defaultValue);
	}

	/**
	 * Validate the request value and return the validated value
	 * @param IRequest $Request
	 * @param $value
	 * @param null $fieldName
	 * @throws \CPath\Request\Parameter\Exceptions\RequiredParameterException
	 * @return mixed validated value
	 */
	function validate(IRequest $Request, $value = null, $fieldName = null) {
		$value = parent::validate($Request, $value, $fieldName);
		if (!$value)
			throw new RequiredParameterException($this, "CLI Argument is required: " . $this->getFieldName());
		return $value;
	}
}