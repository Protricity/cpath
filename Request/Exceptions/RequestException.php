<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Request\Exceptions;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Request\IRequest;
use CPath\Response\IHeaderResponse;
use CPath\Response\IResponse;

class RequestException extends \Exception implements IHeaderResponse, IKeyMap {
    const DEFAULT_HTTP_CODE = IResponse::HTTP_ERROR;
	const STR_TRACE = 'trace';

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

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map instance to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
		$Map->map(IResponse::STR_MESSAGE, $this->getMessage());
		$Map->map(IResponse::STR_CODE, $this->getCode());
		$Map->map(self::STR_TRACE, $this->getTraceAsString());
	}

    // Static
//
//    static function handleException(\Exception $ex) {
//        static $handled = false;
//        if($ex instanceof IResponse) {
//            if($handled)
//                die("FAIL" . $ex);
//            $handled = true;
//            $Request = Request::create('/');
//            $ResponseRenderer = new ResponseRenderer($ex);
//            $ResponseRenderer->render($Request, true);
//        } else {
//            echo $ex;
//        }
//    }

}