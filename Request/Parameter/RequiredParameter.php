<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 11:45 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute\ClassAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Request\RequestException;

class RequiredParameter extends Parameter implements IRenderHTML
{
    const CSS_CLASS = 'required';
    public function __construct($paramName, $description=null) {
        parent::__construct($paramName, $description);
    }

    /**
     * Validate this request or throw a ValidationException
     * @param IRequest $Request
     * @throws RequestException
     * @return mixed validated value
     */
    function validateRequest(IRequest $Request) {
        $value = parent::validateRequest($Request);
        if(!$value)
            throw new RequestException("Param is required: " . $this->getName());
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $Attr = new ClassAttributes(self::CSS_CLASS);
        parent::renderHTML($Request, $Attr->merge($Attr));
    }
}

