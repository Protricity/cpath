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
use CPath\Interfaces\TResponseHelper;
use CPath\Interfaces\IArrayObject;
use CPath\Interfaces\TArrayAccessHelper;

class Response implements IResponse, IArrayObject {
    use TResponseHelper, TArrayAccessHelper;
    private $mCode, $mData=array(), $mMessage;

    function __construct($msg=NULL, $status=true, $data=array()) {
        $this->setStatusCode($status);
        $this->mData = $data;
        $this->mMessage = $msg;
    }

    function getStatusCode() {
        return $this->mCode;
    }

    function setStatusCode($status) {
        if(is_int($status))
            $this->mCode = $status;
        else
            $this->mCode = $status ? IResponse::STATUS_SUCCESS : IResponse::STATUS_ERROR;
        return $this;
    }

    function getMessage() {
        return $this->mMessage;
    }

    function setMessage($msg) {
        $this->mMessage = $msg;
        return $this;
    }

    function update($status, $msg) {
        $this->setMessage($msg);
        $this->setStatusCode($status);
        return $this;
    }

    function &getData() {
        return $this->mData;
    }

    // Statics

    static function getNew($msg=NULL, $status=true, $data=array()) {
        return new self($msg, $status, $data);
    }
}