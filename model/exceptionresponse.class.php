<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model;

use CPath\Base;
use CPath\Interfaces\IHTML;
use CPath\Interfaces\IJSON;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IResponseHelper;
use CPath\Interfaces\IText;
use CPath\Interfaces\IXML;
use CPath\Util;

class ExceptionResponse extends Response {
    /** @var \Exception */
    private $mEx;

    public function __construct(\Exception $ex) {
        $this->mEx = $ex;
        parent::__construct($ex->getMessage(), false);
    }

    function toJSON(Array &$JSON) {
        parent::toJSON($JSON);
        if($this->mEx instanceof IJSON)
            Util::toJSON($this->mEx, $JSON);
        if(Base::isDebug()) {
            $ex = $this->mEx->getPrevious() ?: $this->mEx;
            $trace = $ex->getTraceAsString();
            $JSON['_debug_trace'] = current(explode("\n", $trace));
        }
    }

    function toXML(\SimpleXMLElement $xml)
    {
        parent::toXML($xml);
        if($this->mEx instanceof IXML)
            Util::toXML($this->mEx, $xml);
        if(Base::isDebug()) {
            $ex = $this->mEx->getPrevious() ?: $this->mEx;
            $trace = $ex->getTraceAsString();
            $xml->addChild('_debug_trace', current(explode("\n", $trace)));
        }
    }

    function renderText() {
        parent::renderText();
        if($this->mEx instanceof IText)
            $this->mEx->renderText();
        if(Base::isDebug()) {
            $ex = $this->mEx->getPrevious() ?: $this->mEx;
            $trace = $ex->getTraceAsString();
            echo "Trace: ", current(explode("\n", $trace));
        }
    }

    function renderHtml() {
        parent::renderHtml();
        if($this->mEx instanceof IHTML)
            $this->mEx->renderHtml();
        if(Base::isDebug()) {
            throw $this->mEx->getPrevious() ?: $this->mEx;
        }
    }
}
