<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;

class CodedException extends \Exception implements IResponseCode {
    private $mCode;
    function __construct($message, $statusCode=400, $previous=null) {
        $this->mCode = $statusCode;
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get the Response status code
     * @return int
     */
    function getStatusCode() {
        return $this->mCode;
    }
}