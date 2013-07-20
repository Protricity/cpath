<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Models;
use CPath\Util;
use CPath\Log;
use CPath\LogUser;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseHelper;
use CPath\Interfaces\ILogListener;
use CPath\Interfaces\ILogEntry;

class Response extends ArrayObject implements IResponse, ILogListener {
    private $mCode, $mData=array(), $mMessage;
    /** @var ILogEntry[] */
    private $mLog=array();
    private $mIsLogging=false;

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
        if($this->mIsLogging)
            $status
            ? Log::u(__CLASS__, $msg)
            : Log::e(__CLASS__, $msg);
        return $this;
    }

    function &getData() {
        return $this->mData;
    }

    function startLogging() {
        $this->mIsLogging = true;
        Log::addCallback($this);
        return $this;
    }

    function stopLogging() {
        $this->mIsLogging = false;
        Log::removeCallback($this);
        return $this;
    }

    function onLog(ILogEntry $log) {
        $this->mLog[] = $log;
    }

    function getLog() {
        return $this->mLog;
    }

    function sendHeaders($mimeType=NULL) {
        IResponseHelper::sendHeaders($this, $mimeType);
    }

    function toJSON(Array &$JSON)
    {
        IResponseHelper::toJSON($this, $JSON);
        if($this->mLog) {
            $JSON['log'] = array();
            Util::toJSON($this->mLog, $JSON['log']);
        }
    }

    function toXML(\SimpleXMLElement $xml)
    {
        IResponseHelper::toXML($this, $xml);
        foreach($this->mLog as $log)
            $log->toXML($xml->addChild('log'));
    }

    function __toString() {
        return IResponseHelper::toString($this)
            .implode("\n", $this->mLog);
    }

    // Statics

    static function getNew($msg=NULL, $status=true, $data=array()) {
        return new self($msg, $status, $data);
    }
}
