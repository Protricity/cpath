<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 2:06 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Element\HTMLInputField;
use CPath\Render\HTML\Element\IHTMLInput;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\Validation\IValidation;

class Parameter implements IRequestParameter, IRenderHTML
{
	const CSS_CLASS_ERROR = 'error';

    private $mName, $mDescription, $mValue;
	protected $mFilters = array();

    public function __construct($paramName, $description=null, $defaultValue=null) {
	    $this->mName = $paramName;
	    $this->mDescription = $description;
	    $this->mValue = $defaultValue;
    }

    function getFieldName() {
        return $this->mName;
    }

	/**
	 * Get parameter description
	 * @return String
	 */
	function getDescription() {
		return $this->mDescription;
	}

	protected function getHTMLInput(IRequest $Request, IHTMLInput $Input=null) {
		$Input = $Input ?: new HTMLInputField();
		$Input->setFieldName($this->getFieldName());
		try {
			$value = $this->getInputValue($Request);
			$Input->setInputValue($value);
			$this->validateRequest($Request);

		} catch (\Exception $ex) {
			$Input->addClass(self::CSS_CLASS_ERROR);

		}
		return $Input;
	}

	function addFilter($filter, $options=null, $description=null) {
		$this->mFilters[] = func_get_args();
	}

	function addValidation(IValidation $Validation) {
		$this->mFilters[] = $Validation;
	}

	/**
	 * Get the request value
	 * @param \CPath\Request\IRequest $Request
	 * @throws RequestException if the parameter failed validated
	 * @return mixed
	 */
	function getInputValue(IRequest $Request) {
//		$Params = new SessionParameters($Request);
//		$Params->add($this);
		$name = $this->getFieldName();
		return isset($Request[$name]) ? $Request[$name] : null;
	}

	function tryValue(IRequest $Request, \Exception &$Exception) {
		try {
			return $this->validateRequest($Request);
		} catch (\Exception $ex) {
			$Exception = $ex;
			return null;
		}
	}

	/**
	 * Validate the request and return the validated content
	 * @param IRequest $Request
	 * @return mixed validated content
	 */
	function validateRequest(IRequest $Request) {
		$value = $this->getInputValue($Request);

		$value = $this->filter($Request, $value);
		$this->mValue = $value;
		return $value;
    }

	/**
	 * @param IRequest $Request
	 * @param $value
	 * @return mixed
	 * @throws \CPath\Request\Exceptions\RequestException
	 */
	protected function filter(IRequest $Request, $value) {
		$exs = array();

		foreach($this->mFilters as $Filter) {
			try {
				if($Filter instanceof IValidation) {
					$Filter->validate($Request, $value);

				} else {
					list($filterID, $filterOpts, $desc) = $Filter + array(-1, null, null);
					$value = filter_var($value, $filterID, $filterOpts);
					if($value === false && $filterID !== FILTER_VALIDATE_BOOLEAN) {
						if(!$desc) {
							foreach(filter_list() as $name) {
								if(filter_id($name) === $filterID) {
									$desc = "Filter failed: " . $name;
									break;
								}
							}
							if(!$desc)
								$desc = "Filter failed: " . implode(', ', $Filter);
						}
						throw new RequestException($desc);
					}

				}
			} catch (\Exception $ex) {
				$exs[] = $ex;
			}
		}

		if(sizeof($exs))
			throw new RequestException("Validation exceptions occurred: " . implode("\n\t", $exs));

		return $value;
	}

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
	    $Input = $this->getHTMLInput($Request, $Attr);
	    $Input->renderHTML($Request, $Attr);
	}

	function __invoke(IRequest $Request) {
		return $this->validateRequest($Request);
	}

	// Static

//	static function validate(IRequest $Request) {
//		$Params = new SessionParameters($Request);
//		$Params->validateRequest($Request);
//	}

	static function tryStatic(IRequest $Request, $paramName, $paramDescription=null, $defaultValue=null) {
		$Parameter = new Parameter($paramName, $paramDescription, $defaultValue);
		$Parameter->tryValue($Request, $Exceptions);
		if($Exceptions instanceof \Exception)
			throw $Exceptions;
	}
}

