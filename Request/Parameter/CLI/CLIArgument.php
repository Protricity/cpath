<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/6/14
 * Time: 6:52 PM
 */
namespace CPath\Request\Parameter\CLI;

use CPath\Request\Executable\IPrompt;
use CPath\Request\Form\IFormRequest;
use CPath\Request\IRequest;
use CPath\Request\Parameter\Parameter;

class CLIArgument extends Parameter
{
	private $mArgPos = null;

	public function __construct($index, $optionName, $description=null, $defaultValue=null) {
		$this->mArgPos = $index;
		parent::__construct($optionName, $description, $defaultValue);
	}

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @internal param $value
	 * @return mixed request value
	 */
	function validateRequest(IRequest $Request) {
		$value = $Request->getArgumentValue($this->mArgPos);

		if(!$value) {
			if($Request instanceof IFormRequest)
				$value = $Request->getFormFieldValue($this->getName());
			else
				$value = $Request->getRequestValue($this->getName());
		}

		$value = $this->filter($Request, $value);
		if($value)
			$this->Input->setValue($value);

		return array(
			$this->mArgPos => $value,
			$this->getName() => $value
		);
	}
}