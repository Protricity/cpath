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

    function &getData()
    {
        $ex = $this->getPrevious() ?: $this;
        $arr = array();
        if(Base::isDebug()) {
            $arr['_debug_trace'] = $ex->getTraceAsString();
            $arr['_debug_trace'] = current(explode("\n", $arr['_debug_trace']));
        }
        return $arr;
    }
    function sendHeaders($mimeType=NULL) {
        IResponseHelper::sendHeaders($this, $mimeType);
    }

    function toJSON(Array &$JSON) {
        IResponseHelper::toJSON($this, $JSON);
    }

    function toXML(\SimpleXMLElement $xml)
    {
        IResponseHelper::toXML($this, $xml);
    }

    function __toString() {
        return IResponseHelper::toString($this);
    }
}
