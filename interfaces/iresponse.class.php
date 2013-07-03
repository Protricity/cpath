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
    function sendHeaders($mimeType=NULL);
}

final class IResponseHelper {

    static function toJSON(IResponse $Response, Array &$JSON) {
        $JSON['status'] = $Response->getStatusCode() == IResponse::STATUS_SUCCESS;
        $JSON['msg'] = $Response->getMessage();
        $JSON['response'] = array();
        Util::toJSON($Response->getData(), $JSON['response']);
    }

    static function toXML(IResponse $Response, \SimpleXMLElement $xml) {
        $xml->addAttribute('status', $Response->getStatusCode() == IResponse::STATUS_SUCCESS);
        $xml->addAttribute('msg', $Response->getMessage());
        Util::toXML($Response->getData(), $xml->addChild('response'));
        return $xml;
    }

    static function sendHeaders(IResponse $Response, $mimeType=NULL) {
        $msg = $Response->getMessage();
        //list($msg) = explode("\n", $msg);
        $msg = preg_replace('/[^\w -]/', ' ', $msg);
        header("HTTP/1.0 " . $Response->getStatusCode() . " " . $msg);
        if($mimeType !== NULL)
            header("Content-Type: $mimeType");
    }

    static function toString(IResponse $Response) {
        return
            $Response->getStatusCode() . " " . $Response->getMessage() . "\n"
            . print_r($Response->getData() ?: NULL, true);
    }
}
