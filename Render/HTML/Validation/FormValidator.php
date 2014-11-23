<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 11/13/14
 * Time: 6:58 PM
 */
namespace CPath\Render\HTML\Validation;

use CPath\Render\HTML\Common\RenderableException;
use CPath\Render\HTML\Element\HTMLForm;
use CPath\Request\Common\IInputField;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\Parameter\IRequestParameter;
use CPath\Request\Validation\IValidation;
use CPath\Response\IResponse;

class FormValidator
{
	/** @var HTMLForm */
	private $mForm;

	public function __construct(HTMLForm $Form) {
		$this->mForm = $Form;
	}


	/**
	 * Validate a form request and returns the values
	 * @param IRequest $Request
	 * @param null $paramName
	 * @throws \CPath\Render\HTML\Common\RenderableException
	 * @return array|string
	 */
	function validateRequest(IRequest $Request, $paramName = null) {
		$Form   = $this->mForm;
		$values = array();
		/** @var RequestException[] $Exs */
		$Exs   = array();
		$c     = 0;
		$found = false;
		foreach ($Form->getContentRecursive() as $Content) {
			if($Content instanceof IRequestParameter) {
				$name = $Content->getFieldName();
				$value = $Content->getRequestValue($Request);

			} elseif ($Content instanceof IInputField) {
				$name = $Content->getFieldName();
				$value = $Content->getRequestValue($Request);

			} else {
				continue;
			}

			if ($name === $paramName)
				$found = true;

			if ($Content instanceof IValidation) {
				try {
					$return        = $Content->validate($Request, $value, $name);
					$values[$name] = $return;
					$c++;
				} catch (RequestException $ex) {
					$Exs[]         = $ex;
					$values[$name] = null;
				}
			} else {
				$values[$Content->getFieldName()] = $value;
			}
		}

		if ($paramName && !$found)
			$Exs[] = new \InvalidArgumentException("Form field not found: " . $paramName);

		if ($Exs) {
			$message   = sizeof($Exs) . " Exception(s) occurred during validation:";
			foreach($Exs as $Ex)
				$message .= "\n\t" . $Ex->getMessage();
			$Exception = new RenderableException($Form, $message, IResponse::HTTP_ERROR, $Exs[0]);
			throw $Exception;
		}

		if ($paramName)
			return $values[$paramName];

		return $values;
	}
}