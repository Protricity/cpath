<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Types;
use CPath\Describable\IDescribable;
use CPath\Framework\Data\Compare\IComparable;
use CPath\Framework\Data\Compare\Util\CompareUtil;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IMappableKeys;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Handlers\Response\ResponseUtil;
use CPath\Interfaces\ILogEntry;
use CPath\Model\ArrayObject;

class DataResponse extends ArrayObject implements IResponse, IComparable, IDescribable, IMappableKeys {
    private $mCode, $mData=array(), $mMessage, $mEnableLog = false;
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

    function getCode() {
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

    function getData() {
        return $this->mData;
    }

    /**
     * Return a reference to this object's associative array
     * @return array the associative array
     */
    protected function &getArray() {
        return $this->mData;
    }

    /**
     * @param bool $enabled set to true to enable logging or false to disable
     */
    function setLogging($enabled) {
        $this->mEnableLog = $enabled ? true : false;
    }

    /**
     * Add a log entry to the response
     * @param ILogEntry $Log
     */
    function addLogEntry(ILogEntry $Log) {
        if($this->mEnableLog)
            $this->mLogs[] = $Log;
    }

    /**
     * Get all log entries
     * @return ILogEntry[]
     */
    function getLogs() {
        return $this->mLogs;
    }

    /**
     * Compare two objects
     * @param IComparable $obj the object to compare against $this
     * @return integer < 0 if $obj is less than $this; > 0 if $obj is greater than $this, and 0 if they are equal.
     */
    function compareTo(IComparable $obj)
    {
        if(!$obj instanceof DataResponse)
            return 1;

        $Util = new CompareUtil();
        return
            $Util->compareScalar($this->mCode, $obj->mCode)
            + $Util->compareScalar($this->mMessage, $obj->mMessage)
            + $Util->compare($this->mData, $obj->mData);
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

    /**
     * Map data to a data map
     * @param IKeyMap $Map the map instance to add data to
     * @return void
     */
    function mapKeys(IKeyMap $Map)
    {
        $Util = new ResponseUtil($this);
        $Util->mapKeys($Map);
        //$Map->mapNamedValue(IResponse::JSON_CODE, $this->getCode());
        //$Map->mapNamedValue(IResponse::JSON_MESSAGE, $this->getMessage());
        $Map->map(IResponse::JSON_RESPONSE, $this->mData);
    }

    function __toString() {
        return $this->getTitle();
    }

    // Statics
}
