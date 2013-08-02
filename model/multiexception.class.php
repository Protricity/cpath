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

class MultiException extends ResponseException implements \Countable {
    private $mEx = array();
    public function add($ex, $key=NULL) {
        if($ex instanceof \Exception)
            $ex = $ex->getMessage();
        $this->message = ($this->message ? $this->message."\n" : "") . $ex;
        if(is_int($key))
            $this->mEx[] = $ex;
        else
            $this->mEx[$key] = $ex;
    }

    public function count()
    {
        return count($this->mEx);
    }

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


    function toJSON(Array &$JSON) {
        IResponseHelper::toJSON($this, $JSON);
        $JSON['response'] = array();
        foreach($this->mEx as $field => $ex)
            $JSON['response'] = array('msg' => $ex, 'field' => $field);
        if(Base::isDebug()) {
            $ex = $this->getPrevious() ?: $this;
            $trace = $ex->getTraceAsString();
            $JSON['_debug_trace'] = current(explode("\n", $trace));
        }
    }

    function toXML(\SimpleXMLElement $xml)
    {
        IResponseHelper::toXML($this, $xml);
        $rxml = $xml->addChild('response');
        foreach($this->mEx as $field => $ex)
            $rxml->addChild('error', $ex)
                ->addAttribute('field', $field);
        if(Base::isDebug()) {
            $ex = $this->getPrevious() ?: $this;
            $trace = $ex->getTraceAsString();
            $xml->addChild('_debug_trace', current(explode("\n", $trace)));
        }
    }
}