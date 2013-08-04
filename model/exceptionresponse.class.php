<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;

use CPath\Base;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IResponseHelper;
use CPath\Interfaces\IXML;

class ExceptionResponse extends Response {
    /** @var \Exception */
    private $mEx;

    public function __construct(\Exception $ex) {
        $this->mEx = $ex;
        parent::__construct($ex->getMessage(), false);
    }

    function toJSON(Array &$JSON) {
        parent::toJSON($JSON);
        if(Base::isDebug()) {
            $ex = $this->mEx->getPrevious() ?: $this->mEx;
            $trace = $ex->getTraceAsString();
            $JSON['_debug_trace'] = current(explode("\n", $trace));
        }
    }

    function toXML(\SimpleXMLElement $xml)
    {
        parent::toXML($xml);
        if(Base::isDebug()) {
            $ex = $this->mEx->getPrevious() ?: $this->mEx;
            $trace = $ex->getTraceAsString();
            $xml->addChild('_debug_trace', current(explode("\n", $trace)));
        }
    }
}
