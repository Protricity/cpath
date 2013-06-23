<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;
use CPath\Util;
interface IResponse extends IJSON,IXML {
    const STATUS_SUCCESS = 200;
    const STATUS_ERROR = 400;
    function getStatusCode();
    function getMessage();
    function &getData();
    function sendHeaders();
}

trait TResponseHelper {

    function toJSON(Array &$JSON) {
        $JSON['status'] = $this->getStatusCode() == IResponse::STATUS_SUCCESS;
        $JSON['msg'] = $this->getMessage();
        $JSON['response'] = array();
        Util::toJSON($this->getData(), $JSON['response']);
    }

    function toXML(\SimpleXMLElement $xml) {
        $xml->addAttribute('status', $this->getStatusCode() == IResponse::STATUS_SUCCESS);
        $xml->addAttribute('msg', $this->getMessage());
        Util::toXML($this->getData(), $xml->addChild('response'));
        return $xml;
    }

    function sendHeaders($mimeType=NULL) {
        $msg = $this->getMessage();
        //list($msg) = explode("\n", $msg);
        $msg = preg_replace('/[^\w\s-]/', ' ', $msg);
        header("HTTP/1.0 " . $this->getStatusCode() . " " . $msg);
        if($mimeType !== NULL)
            header("Content-Type: $mimeType");
    }

    function __toString() {
        return
            $this->getStatusCode() . " " . $this->getMessage() . "\n"
            . print_r($this->getData(), true);
    }
}