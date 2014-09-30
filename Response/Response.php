<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;

use CPath\Request\IRequest;

class Response implements IHeaderResponse {
    private $mCode, $mMessage;

    /**
     * Create a new response
     * @param String $msg the response message
     * @param int|bool $status the response status code or true/false for success/error
     * @internal param mixed $data additional response data
     */
    function __construct($msg=NULL, $status=true) {
        $this->setStatusCode($status);
        $this->setMessage($msg);
    }

    function getCode() {
        return $this->mCode;
    }

    /**
     * @param int|bool $status
     * @return $this
     */
    function setStatusCode($status) {
        if(is_int($status))
            $this->mCode = $status;
        else
            $this->mCode = $status ? IResponse::STATUS_SUCCESS : IResponse::STATUS_ERROR;
        return $this;
    }

    /**
     * Get the Response Message
     * @return String
     */
    function getMessage() {
        return $this->mMessage;
    }

    /**
     * Set the message and return the Response
     * @param $msg
     * @return $this
     */
    function setMessage($msg) {
        $this->mMessage = $msg;
        return $this;
    }

    /**
     * Update and return the Response
     * @param $status
     * @param $msg
     * @return $this
     */
    function update($status=null, $msg=null) {
        if($msg !== null)
            $this->setMessage($msg);
        if($status !== null)
            $this->setStatusCode($status);
        return $this;
    }


    /**
     * Send response headers for this response
     * @param IRequest $Request
     * @param string $mimeType
     * @return bool returns true if the headers were sent, false otherwise
     */
    function sendHeaders(IRequest $Request, $mimeType = null) {
        static $sent = false;
        if($sent || headers_sent())
            return false;
        $sent = true;

        if($mimeType === null)
            $mimeType = $Request->getMimeType()->getName();

        header("HTTP/1.1 " . $this->getCode() . " " . preg_replace('/[^\w -]/', '', $this->getMessage()));
        header("Content-Type: " . $mimeType);

        header('Access-Control-Allow-Origin: *');

        return true;
    }
}