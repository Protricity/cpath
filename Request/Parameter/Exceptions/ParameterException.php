<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 5:16 PM
 */
namespace CPath\Request\Parameter\Exceptions;

use CPath\Request\Parameter\IParameterMap;
use CPath\Response\Exceptions\HTTPRequestException;

class RequestParameterException extends HTTPRequestException
{
    public function __construct($message) {
        parent::__construct($message);
    }
}