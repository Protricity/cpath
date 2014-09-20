<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Types;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IMappableKeys;
use CPath\Framework\Response\Interfaces\IResponse;

final class SimpleResponse implements IResponse, IMappableKeys {
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
     * @param IKeyMap $Map the map instance to add data to
     * @return void
     */
    function mapKeys(IKeyMap $Map) {
        $Map->map(IResponse::STR_CODE, $this->getCode());
        $Map->map(IResponse::STR_MESSAGE, $this->getMessage());
    }
}