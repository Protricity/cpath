<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;
use CPath\Base;
use CPath\Log;
use CPath\Util;
interface IResponse extends IJSON,IXML {
    const STATUS_SUCCESS = 200;
    const STATUS_ERROR = 400;

    /**
     * Get the Response status code
     * @return int
     */
    function getStatusCode();

    /**
     * Get the IResponse Message
     * @return String
     */
    function getMessage();

    /**
     * @param mixed|NULL $_path optional varargs specifying a path to data
     * Example: ->getData(0, 'key') gets $data[0]['key'];
     * @return mixed the data array or targeted data specified by path
     * @throws \InvalidArgumentException
     */
    function &getData($_path=NULL);

    /**
     * Send response headers for this request
     * @param null $mimeType
     * @return mixed
     */
    function sendHeaders($mimeType=NULL);
}

final class IResponseHelper {

    static function toJSON(IResponse $Response, Array &$JSON) {
        $JSON['status'] = $Response->getStatusCode() == IResponse::STATUS_SUCCESS;
        $JSON['msg'] = $Response->getMessage();
        if($data = $Response->getData()) {
            $JSON['response'] = array();
            Util::toJSON($data, $JSON['response']);
        }
    }

    static function toXML(IResponse $Response, \SimpleXMLElement $xml) {
        $xml->addChild('status', $Response->getStatusCode() == IResponse::STATUS_SUCCESS ? 1 : 0);
        $xml->addChild('msg', $Response->getMessage());
        if($data = $Response->getData())
            Util::toXML($data, $xml->addChild('response'));
        return $xml;
    }

    static function sendHeaders(IResponse $Response, $mimeType=NULL) {
        if(Util::isCLI())
            return;
        if(headers_sent($file, $line)) {
            Log::e("IResponseHelper", "Warning: Headers already sent by {$file}:{$line}");
            return;
        }
        $msg = $Response->getMessage();
        //list($msg) = explode("\n", $msg);
        $msg = preg_replace('/[^\w -]/', '', $msg);
        header("HTTP/1.1 " . $Response->getStatusCode() . " " . $msg);
        if($mimeType !== NULL)
            header("Content-Type: $mimeType");
        header('Access-Control-Allow-Origin: *');
    }

    static function toString(IResponse $Response) {
        return
            $Response->getStatusCode() . " " . $Response->getMessage()
            . (Base::isDebug() ? "\n" . print_r($Response->getData() ?: NULL, true) : ''); // TODO: IText
    }
}
