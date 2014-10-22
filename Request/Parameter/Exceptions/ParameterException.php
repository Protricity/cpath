<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 5:16 PM
 */
namespace CPath\Request\Parameter\Exceptions;

use CPath\Request\Parameter\IParameterMap;
use CPath\Request\Exceptions\HTTPRequestException;

class RequestParameterException extends \CPath\Request\Exceptions\HTTPRequestException
{
    public function __construct($message) {
        parent::__construct($message);
    }
}