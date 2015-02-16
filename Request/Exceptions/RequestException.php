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
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\IResponseHeaders;
use CPath\Response\Response;

class RequestException extends \Exception implements IResponse, IResponseHeaders, IKeyMap {
    const DEFAULT_HTTP_CODE = IResponse::HTTP_ERROR;
	const STR_TRACE = 'trace';
	const STR_CLASS = 'class';

	private $mResponse = null;

    function __construct($message, $statusCode=null, \Exception $previous=null) {
        static $handlerSet = false;
        if(!$handlerSet) {
            set_exception_handler(__CLASS__ . '::handleException');
            $handlerSet = true;
        }
        parent::__construct($message, $statusCode ?: static::DEFAULT_HTTP_CODE, $previous);
    }

	/**
	 * Add response headers to this response object
	 * @param String $name i.e. 'Location' or 'Location: /path'
	 * @param String|null $value i.e. '/path'
	 * @return $this
	 */
	function addHeader($name, $value=null) {
		$Response = $this->mResponse
			?: $this->mResponse = new Response($this->getMessage(), $this->getCode());
		$Response->addHeader($name, $value);
		return $this;
	}

	/**
	 * Send response headers for this response
	 * @param IRequest $Request
	 * @param string $mimeType
	 * @return bool returns true if the headers were sent, false otherwise
	 */
	function sendHeaders(IRequest $Request, $mimeType = null) {
		$Response = $this->mResponse
			?: $this->mResponse = new Response($this->getMessage(), $this->getCode());
		return $Response->sendHeaders($Request, $mimeType);
	}

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
		$Renderer = new ExceptionResponse($this);
		$Renderer->mapKeys($Map);
	}

	// Static

	static function handleException(\Exception $Ex) {
		$msg = $Ex->getMessage();
		$msg =  preg_replace('/[^\w -]/', '', $msg);
		if(strlen($msg) > 64)
			$msg = substr($msg, 0, 64) . '...';

		header("HTTP/1.1 " . $Ex->getCode() . " " . $msg);
		echo $Ex;
	}
}