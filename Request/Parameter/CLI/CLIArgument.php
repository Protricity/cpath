<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/6/14
 * Time: 6:52 PM
 */
namespace CPath\Request\Parameter\CLI;

use CPath\Request\IRequest;
use CPath\Request\Parameter\FormField;

class CLIArgument extends FormField
{
	private $mArgPos = null;

	public function __construct($index, $optionName, $description=null, $defaultValue=null) {
		$this->mArgPos = $index;
		parent::__construct($optionName, $description, $defaultValue);
	}

	/**
	 * Get the request value
	 * @param \CPath\Request\IRequest $Request
	 * @return mixed
	 */
	function getInputValue(IRequest $Request) {
		return parent::getInputValue($Request)
			?: (isset($Request[$this->mArgPos])
			? $Request[$this->mArgPos]
			: null);
	}
}