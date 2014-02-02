<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;
use CPath\Base;
use CPath\Config;
use CPath\Log;
use CPath\Util;

final class ResponseUtil {
    private $mResponse;

    function __construct(IResponse $Response) {
        $this->mResponse = $Response;
    }

    function toJSON(Array &$JSON) {
        $Response = $this->mResponse;
        $JSON['status'] = $Response->getStatusCode() == \CPath\Response\IResponse::STATUS_SUCCESS;
        $JSON['msg'] = $Response->getMessage();
        if($data = $Response->getDataPath()) {
            $JSON['response'] = array();
            Util::toJSON($data, $JSON['response']);
        }
        if($logs = $Response->getLogs()) {
            $JSON['log'] = array();
            Util::toJSON($logs, $JSON['log']);
        }
    }

    function toXML(\SimpleXMLElement $xml) {
        $Response = $this->mResponse;
        $xml->addChild('status', $Response->getStatusCode() == \CPath\Response\IResponse::STATUS_SUCCESS ? 1 : 0);
        $xml->addChild('msg', $Response->getMessage());
        if($data = $Response->getDataPath())
            Util::toXML($data, $xml->addChild('response'));
        if($logs = $Response->getLogs()) {
            foreach($logs as $log)
                $log->toXML($xml->addChild('log'));
        }
        return $xml;
    }

    function renderText() {
        $Response = $this->mResponse;
        echo "Status:  ", $Response->getStatusCode(), "\n";
        echo "Message: ", $Response->getMessage(), "\n";
        print_r($Response->getDataPath()); // TODO: make pretty and safe
    }

    function renderHtml() {
        $Response = $this->mResponse;
        echo "<pre>";
        echo "Status:  ", $Response->getStatusCode(), "\n";
        echo "Message: ", htmlentities($Response->getMessage()), "\n";
        htmlentities(print_r($Response->getDataPath(), true)); // TODO: make pretty and safe
        echo "</pre>";
    }

    function sendHeaders($mimeType=NULL) {
        $Response = $this->mResponse;
        if(Base::isCLI())
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

    function toString() {
        $Response = $this->mResponse;
        return
            $Response->getStatusCode() . " " . $Response->getMessage()
            . (Config::$Debug ? "\n" . print_r($Response->getDataPath() ?: NULL, true) : ''); // TODO: IText
    }

    // Static

    static function get(IResponse $Response) {
        return new static($Response);
    }
}