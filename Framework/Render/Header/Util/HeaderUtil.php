<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/23/14
 * Time: 2:00 PM
 */

namespace CPath\Framework\Render\Header\Util;


class HeaderUtil {
    private $mMimeType;

    public function __construct($mimeType) {
        $this->mMimeType = $mimeType;
    }

    /**
     * Send headers associated with this response
     * @param null $mimeType
     * @return bool true if headers were sent, false otherwise
     */
    function sendHeaders($mimeType=NULL) {
        if(headers_sent())
            return false;

        $msg = preg_replace('/[^\w -]/', '', $this->getMessage());

        header("HTTP/1.1 " . $this->getStatusCode() . " " . $msg);
        if($mimeType !== NULL)
            header("Content-Type: $mimeType");
        header('Access-Control-Allow-Origin: *');
        self::$mSent = true;
        return true;
    }
} 