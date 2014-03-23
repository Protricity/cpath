<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Types;

use CPath\Config;
use CPath\Framework\Data\Map\Associative\Interfaces\IAssociativeMap;
use CPath\Framework\Data\Map\Interfaces\IDataMap;
use CPath\Framework\Data\Map\Interfaces\IMappable;
use CPath\Framework\Data\Map\Tree\Interfaces\IAssociativeTree;
use CPath\Framework\Data\Map\Types\ArrayMap;
use CPath\Framework\Exception\Util\ExceptionUtil;
use CPath\Framework\Response\Exceptions\CodedException;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Interfaces\IResponseCode;


class ExceptionResponse implements IResponse, IMappable {
    /** @var \Exception */
    private $mEx, $mCode;
    public function __construct(\Exception $ex) {
        $this->mEx = $ex;
        $this->mCode = IResponseCode::STATUS_ERROR;
        if($ex instanceof CodedException)
            $this->mCode = $ex->getCode();
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
        $Map->mapSubsection('error', new ExceptionUtil($this->mEx));
    }

    /**
     * Get the IResponse Message
     * @return String
     */
    function getMessage() {
        return $this->mEx->getMessage();
    }

    /**
     * Get the DataResponse status code
     * @return int
     */
    function getCode() {
        return $this->mCode;
    }

    function getException() {
        return $this->mEx;
    }

//    function toJSON(Array &$JSON) {
//        parent::toJSON($JSON);
//        if($this->mEx instanceof IJSON)
//            Util::toJSON($this->mEx, $JSON);
//        if(Config::$Debug) {
//            $ex = $this->mEx->getPrevious() ?: $this->mEx;
//            $trace = $ex->getTraceAsString();
//            $JSON['_debug_trace'] = $trace; //current(explode("\n", $trace));
//        }
//    }
//
//    function toXML(\SimpleXMLElement $xml)
//    {
//        parent::toXML($xml);
//        if($this->mEx instanceof IXML)
//            Util::toXML($this->mEx, $xml);
//        if(Config::$Debug) {
//            $ex = $this->mEx->getPrevious() ?: $this->mEx;
//            $trace = $ex->getTraceAsString();
//            $xml->addChild('_debug_trace', current(explode("\n", $trace)));
//        }
//    }
//
//    function renderText() {
//        parent::renderText();
//        if($this->mEx instanceof IText)
//            $this->mEx->renderText();
//        if(Config::$Debug) {
//            $ex = $this->mEx->getPrevious() ?: $this->mEx;
//            $trace = $ex->getTraceAsString();
//            echo "Trace: ", current(explode("\n", $trace));
//        }
//    }
//
//    function renderHtml() {
//        parent::renderHtml();
//        if($this->mEx instanceof IHTML)
//            $this->mEx->renderHtml();
//        if(Config::$Debug) {
//            throw $this->mEx->getPrevious() ?: $this->mEx;
//        }
//    }
}
