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
use CPath\Interfaces\IResponseHelper;
use CPath\Interfaces\IText;
use CPath\Interfaces\IXML;

class MultiException extends \Exception implements \Countable, IJSON, IXML, IText, IHTML {
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

    public function count() {
        return count($this->mEx);
    }

    function toJSON(Array &$JSON) {
        $JSON['errors'] = array();
        foreach($this->mEx as $field => $ex)
            $JSON['errors'][] = array('msg' => $ex, 'field' => $field);
    }

    function toXML(\SimpleXMLElement $xml) {
        $rxml = $xml->addChild('errors');
        foreach($this->mEx as $field => $ex)
            $rxml->addChild('error', $ex)
                ->addAttribute('field', $field);
    }

    function renderText() {
        $max = 0;
        foreach($this->mEx as $field => $ex)
            if(strlen($field) > $max) $max = strlen($field);
        foreach($this->mEx as $field => $ex)
            echo "(", str_pad($field, $max, ' '), ") ", $ex, "\n";
    }

    function renderHtml() {
        $max = 0;
        foreach($this->mEx as $field => $ex)
            if(strlen($field) > $max) $max = strlen($field);
        foreach($this->mEx as $field => $ex)
            echo "(", str_pad($field, $max, ' '), ") ", $ex, "<br />";
    }
}