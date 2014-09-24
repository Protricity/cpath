<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 6:54 PM
 */
namespace CPath\Request\Validation;

use CPath\Response\Exceptions\CodedException;
use CPath\Response\IResponseCode;

class ValidationException extends CodedException
{
    private $mParamName;

    public function __construct($msg, $paramName) {
        parent::__construct($msg, IResponseCode::STATUS_ERROR);
        $this->mParamName = $paramName;
    }


}

