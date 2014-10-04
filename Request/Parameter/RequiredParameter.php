<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 11:45 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\RequestException;

class RequiredParameter extends Parameter implements IRenderHTML
{
    const CSS_CLASS_REQUIRED = 'required';

	public function __construct($paramName, $description=null, $defaultValue=null) {
		parent::__construct($paramName, $description, $defaultValue);
		$this->Input->addClass(self::CSS_CLASS_REQUIRED);
	}


	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @param $value
	 * @throws \CPath\Request\RequestException
	 * @return mixed request value
	 */
	function validate(IRequest $Request, $value) {
		//$value = $Request->getValue($this->getName(), $this->mDescription);
		$value = parent::validate($Request, $value);
		if (!$value) {
			$this->Label->addClass(self::CSS_CLASS_ERROR);
			throw new RequestException("Parameter is required: " . $this->getName());
		}
		return $value;
	}
}

