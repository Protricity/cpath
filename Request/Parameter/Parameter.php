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
use CPath\Request\Parameter\IRequestParameter;

class Parameter implements IRequestParameter
{
	const CSS_CLASS_ERROR = 'error';

    private $mDescription;
    protected $Input;
	protected $Label;

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

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @param $value
	 * @throws \CPath\Request\RequestException
	 * @return mixed request value
	 */
	function validate(IRequest $Request, $value) {
		if($value)
            $this->Input->setValue($value);
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
		    $this->Input->setValue($Request->getValue($this));
        $this->Label->renderHTML($Request, $Attr);
	}
}

