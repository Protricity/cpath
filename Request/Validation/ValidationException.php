<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 6:54 PM
 */
namespace CPath\Request\Validation;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Response\Exceptions\HTTPRequestException;

class ValidationException extends HTTPRequestException
{

    private $mValidation;

    public function __construct($msg, IValidateRequest $Validation=null) {
        parent::__construct($msg);
        $this->mValidation = $Validation;
    }

}

