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
use CPath\Render\HTML\Element\HTMLLabel;
use CPath\Request\IRequest;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\Validation\IRequestValidation;

class Parameter implements IRequestParameter
{
	const CSS_CLASS_ERROR = 'error';

    private $mDescription;
    protected $Input;
	protected $Label;
	protected $mFilters = array();

    public function __construct($paramName, $description=null, $defaultValue=null) {
        $this->Input = new HTMLInputField($defaultValue);
        $this->Input->setName($paramName);

        $this->Label = new HTMLLabel($description ?: $paramName);
        $this->Label->addContent($this->Input);

        $this->mDescription = $description;
    }

    function getName() {
        return $this->Input->getName();
    }

	/**
	 * Get parameter description
	 * @return String
	 */
	function getDescription() {
		return $this->mDescription;
	}

	function addFilter($filter, $options=null, $description=null) {
		$this->mFilters[] = func_get_args();
	}

	function addValidation(IRequestValidation $Validation) {
		$this->mFilters[] = $Validation;
	}

	/**
	 * Validate the request and return the validated content
	 * @param IRequest $Request
	 * @return mixed validated content
	 */
	function validateRequest(IRequest $Request) {
		$name = $this->getName();
		$value = $Request->getArgumentValue($name)
			?: $Request->getRequestValue($name);

		$value = $this->filter($Request, $value);
		if($value)
			$this->Input->setValue($value);
		return $value;
    }

	/**
	 * @param IRequest $Request
	 * @param $value
	 * @return mixed
	 * @throws \CPath\Request\Exceptions\RequestException
	 */
	protected function filter(IRequest $Request, $value) {
		foreach($this->mFilters as $Filter) {
			if($Filter instanceof IRequestValidation) {
				$Filter->validateRequest($Request);

			} else {
				list($filterID, $filterOpts, $desc) = $Filter;
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
		}
		return $value;
	}

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
	    if(!$this->Input->hasAttribute('value'))
		    $this->Input->setValue($Request->getRequestValue($this));
        $this->Label->renderHTML($Request, $Attr);
	}
}

