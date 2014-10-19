<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/6/14
 * Time: 6:52 PM
 */
namespace CPath\Request\Parameter\CLI;

use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Parameter\CLI\CLIArgument;
use CPath\Request\Exceptions\RequestException;

class CLIRequiredArgument extends CLIArgument
{
	public function __construct($index, $optionName, $description=null, $defaultValue=null) {
		parent::__construct($index, $optionName, $description, $defaultValue);
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @throws \CPath\Request\Exceptions\RequestException
	 * @internal param $value
	 * @return mixed request value
	 */
	function validateRequest(IRequest $Request) {
		$value = parent::validateRequest($Request);
		if (!$value) {
			$this->Label->addClass(static::CSS_CLASS_ERROR);
			throw new RequestException("Parameter is required: " . $this->getName());
		}
	}
}