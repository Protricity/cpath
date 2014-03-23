<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Types;
use CPath\Framework\Data\Map\Associative\Interfaces\IAssociativeMap;
use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\Response\Interfaces\IResponse;

abstract class AbstractResponse implements IResponse, IMappable {
    private $mCode, $mMessage;

    /**
     * Create a new response
     * @param String $msg the response message
     * @param bool $status the response status
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
     * Map data to a data map
     * @param IDataMap $Map the map instance to add data to
     * @return void
     */
    function mapData(IDataMap $Map)
    {
        $Map->mapKeyValue(IResponse::JSON_CODE, $this->getCode());
        $Map->mapKeyValue(IResponse::JSON_MESSAGE, $this->getMessage());
    }
}