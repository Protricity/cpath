<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;
use CPath\Interfaces\IComparable;
use CPath\Interfaces\IComparator;
use CPath\Describable\IDescribable;
use CPath\Interfaces\ILogEntry;
use CPath\Interfaces\NotEqualException;
use CPath\Model\ArrayObject;

class Response extends ArrayObject implements IResponse, IComparable, IDescribable {
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
        ResponseUtil::get($this)
            ->sendHeaders($mimeType);
    }

    function toJSON(Array &$JSON) {
        ResponseUtil::get($this)
            ->toJSON($this, $JSON);
    }

    function toXML(\SimpleXMLElement $xml) {
        ResponseUtil::get($this)
            ->toXML($this, $xml);
    }

    /**
     * Render Object as HTML
     * @return void
     */
    function renderHtml() {
        ResponseUtil::get($this)
            ->renderHtml($this);
    }

    /**
     * Render Object as Plain Text
     * @return void
     */
    function renderText() {
        ResponseUtil::get($this)
            ->renderText($this);
    }

    /**
     * Compare two instances of this object
     * @param IComparable|Response $obj the object to compare against $this
     * @param IComparator $C the IComparator instance
     * @throws NotEqualException if the objects were not equal
     * @return void
     */
    function compareTo(IComparable $obj, IComparator $C) {
        $C->compareScalar($this->mCode, $obj->mCode, "Response Status");
        $C->compareScalar($this->mMessage, $obj->mMessage, "Response Message");
        $C->compare($this->mData, $obj->mData, "Response Data");
    }

    /**
     * Get a simple public-visible title of this object as it would be displayed in a header (i.e. "Mr. Root")
     * @return String title for this Object
     */
    function getTitle() {
        return $this->getMessage();
    }

    /**
     * Get a simple public-visible description of this object as it would appear in a paragraph (i.e. "User account 'root' with ID 1234")
     * @return String simple description for this Object
     */
    function getDescription() {
        return $this->getMessage();
    }

    function __toString() {
        return $this->getTitle();
    }

    // Statics
}
