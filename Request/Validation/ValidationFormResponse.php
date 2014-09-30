<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/28/14
 * Time: 10:38 AM
 */
namespace CPath\Request\Validation;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

class ValidationFormResponse extends \Exception implements IValidationException, IResponse, IRenderHTML
{
    private $mForm;

    public function __construct(ValidationForm $Form, $message, $code = null) {
        parent::__construct($message, $code);
        $this->mForm = $Form;
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param Attribute\IAttributes $Attr
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $this->mForm->renderHTML($Request, $Attr);
    }

    /**
     * @return IValidateRequest
     */
    function getValidation() {
        return $this->mForm;
    }
}