<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Models;
use CPath\Util;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseHelper;

class Response implements IResponse {
    use IResponseHelper;
    private $mCode, $mData, $mMessage;

    function __construct(Array $response, $msg=NULL, $success=true) {
        if(is_int($success))
            $this->mCode = $success;
        else
            $this->mCode = $success ? 200 : 404;

        $this->mData = $response;
        $this->mMessage = $msg;
    }

    function getStatusCode() {
        return $this->mCode;
    }

    function getMessage() {
        return $this->mMessage;
    }

    function getData() {
        return $this->mData;
    }

    function __toString() {
        return
            $this->getStatusCode() . " " . $this->getMessage() . "\n"
            . print_r($this->getData(), true);
    }
}