<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;

use CPath\Base;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseHelper;

class ResponseException extends \Exception implements IResponse {

    function getStatusCode() { return IResponse::STATUS_ERROR; }

    /**
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getData(0, 'key') gets $data[0]['key'];
     * @return mixed the data array or targeted data specified by path
     * @throws \InvalidArgumentException if the data path doesn't exist
     */
    function &getData($_path=NULL)
    {
        if($_path !== NULL)
            throw new \InvalidArgumentException("Invalid data path: No data available");
        $arr = array();
        return $arr;
    }

    function sendHeaders($mimeType=NULL) {
        IResponseHelper::sendHeaders($this, $mimeType);
    }

    function toJSON(Array &$JSON) {
        IResponseHelper::toJSON($this, $JSON);
        if(Base::isDebug()) {
            $ex = $this->getPrevious() ?: $this;
            $trace = $ex->getTraceAsString();
            $JSON['_debug_trace'] = current(explode("\n", $trace));
        }
    }

    function toXML(\SimpleXMLElement $xml)
    {
        IResponseHelper::toXML($this, $xml);
        if(Base::isDebug()) {
            $ex = $this->getPrevious() ?: $this;
            $trace = $ex->getTraceAsString();
            $xml->addChild('_debug_trace', current(explode("\n", $trace)));
        }
    }

    function __toString() {
        return IResponseHelper::toString($this);
    }
}
