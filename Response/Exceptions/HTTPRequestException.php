<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response\Exceptions;

use CPath\Request\IRequest;
use CPath\Request\Request;
use CPath\Response\IHeaderResponse;
use CPath\Response\IResponse;
use CPath\Response\ResponseRenderer;

class HTTPRequestException extends \Exception implements IHeaderResponse {
    const DEFAULT_HTTP_CODE = IResponse::HTTP_ERROR;

    function __construct($message, $statusCode=null) {
        static $handlerSet = false;
        if(!$handlerSet) {
            //set_exception_handler(__CLASS__ . '::handleException');
            $handlerSet = true;
        }
        parent::__construct($message, $statusCode ?: static::DEFAULT_HTTP_CODE);
    }

    /**
     * Send response headers for this response
     * @param IRequest $Request
     * @param string $mimeType
     * @return bool returns true if the headers were sent, false otherwise
     */
    function sendHeaders(IRequest $Request, $mimeType = null) {
        static $sent = false;
        if($sent || headers_sent())
            return false;
        $sent = true;

        if($mimeType === null)
            $mimeType = $Request->getMimeType()->getName();

        header("HTTP/1.1 " . $this->getCode() . " " . preg_replace('/[^\w -]/', '', $this->getMessage()));
        header("Content-Type: " . $mimeType);

        header('Access-Control-Allow-Origin: *');

        return true;
    }


    // Static

    static function handleException(\Exception $ex) {
        static $handled = false;
        if($ex instanceof IResponse) {
            if($handled)
                die("FAIL" . $ex);
            $handled = true;
            $Request = Request::create('/');
            $ResponseRenderer = new ResponseRenderer($ex);
            $ResponseRenderer->render($Request, true);
        } else {
            echo $ex;
        }
    }
}