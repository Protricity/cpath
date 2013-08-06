<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;
use CPath\Util;
use CPath\Log;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseHelper;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\ILogEntry;

class Response extends ArrayObject implements IResponse {
    private $mCode, $mData=array(), $mMessage;
    /** @var ILogEntry[] */
    private $mLogs=array();

    /**
     * Create a new response
     * @param String $msg the response message
     * @param bool $status the response status
     * @param mixed $data additional response data
     */
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

    function update($status, $msg, $data=NULL) {
        $this->setMessage($msg);
        $this->setStatusCode($status);
        if($data) $this->setData($data);
//        if($this->mIsLogging)
//            $status
//            ? Log::u(__CLASS__, $msg)
//            : Log::e(__CLASS__, $msg);
        return $this;
    }

    function setData($data) {
        $this->mData = $data;
        return $this;
    }

    /**
     * Return a reference to this object's associative array
     * @return array the associative array
     */
    protected function &getArray() {
        return $this->mData;
    }

    /**
     * Add a log entry to the response
     * @param ILogEntry $Log
     */
    function addLogEntry(ILogEntry $Log) {
        $this->mLogs[] = $Log;
    }

    /**
     * Get all log entries
     * @return ILogEntry[]
     */
    function getLogs() {
        return $this->mLogs;
    }

    function sendHeaders($mimeType=NULL) {
        IResponseHelper::sendHeaders($this, $mimeType);
    }

    function toJSON(Array &$JSON) {
        IResponseHelper::toJSON($this, $JSON);
    }

    function toXML(\SimpleXMLElement $xml) {
        IResponseHelper::toXML($this, $xml);
    }

    /**
     * Render Object as HTML
     * @return void
     */
    function renderHtml() {
        IResponseHelper::renderHtml($this);
    }

    /**
     * Render Object as Plain Text
     * @return void
     */
    function renderText() {
        IResponseHelper::renderText($this);
    }

    // Statics

    /**
     * Return a new response
     * @param String $msg the response message
     * @param bool $status the response status
     * @param mixed $data additional response data
     * @return Response a new Response instance
     */
    static function getNew($msg=NULL, $status=true, $data=array()) {
        return new self($msg, $status, $data);
    }
}
