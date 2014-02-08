<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Exceptions;


class CodedException extends \Exception implements \CPath\Framework\Response\Interfaces\IResponseCode {
    const DEFAULT_CODE = 400;

    function __construct($message, $statusCode=null, $previous=null) {
        parent::__construct($message, $statusCode ?: static::DEFAULT_CODE, $previous);
    }

    /**
     * Get the Response status code
     * @return int
     */
    function getStatusCode() {
        return $this->getCode();
    }
}