<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Exceptions;


use CPath\Framework\Response\Interfaces\IResponseCode;

class CodedException extends \Exception implements IResponseCode {
    const DEFAULT_CODE = 400;

    function __construct($message, $statusCode=null, $previous=null) {
        parent::__construct($message, $statusCode ?: static::DEFAULT_CODE, $previous);
    }
}