<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 11:54 PM
 */
namespace CPath\Request\Exceptions;

class FormFieldException extends RequestException
{
    public function __construct($msg, $fieldName, $description=null) {
        Exceptions\parent::__construct($msg);
    }
}

