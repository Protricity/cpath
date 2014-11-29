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
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Response\IHeaderResponse;
use CPath\Response\IResponse;
use CPath\Response\ResponseRenderer;

class RequestException extends \Exception implements IHeaderResponse, IKeyMap, IRenderHTML, IHTMLSupportHeaders {
    const DEFAULT_HTTP_CODE = IResponse::HTTP_ERROR;
	const STR_TRACE = 'trace';

	/** @var IRenderHTML */
	private $mRenderable = null;

    function __construct($message, $statusCode=null, \Exception $previous=null) {
        static $handlerSet = false;
        if(!$handlerSet) {
            //set_exception_handler(__CLASS__ . '::handleException');
            $handlerSet = true;
        }
        parent::__construct($message, $statusCode ?: static::DEFAULT_HTTP_CODE, $previous);
    }

	public function setRenderable(IRenderHTML $Renderable) {
		$this->mRenderable = $Renderable;
	}

    /**
     * Send response headers for this response
     * @param IRequest $Request
     * @param string $mimeType
     * @return bool returns true if the headers were sent, false otherwise
     */
    function sendHeaders(IRequest $Request, $mimeType = null) {
	    $ResponseRenderer = new ResponseRenderer($this);
	    return $ResponseRenderer->sendHeaders($Request, $mimeType);
    }

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
		$Map->map(IResponse::STR_MESSAGE, $this->getMessage());
		$Map->map(IResponse::STR_CODE, $this->getCode());
		$Map->map(self::STR_TRACE, $this->getTraceAsString());
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		if($this->mRenderable instanceof IHTMLSupportHeaders)
			$this->mRenderable->writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		if($this->mRenderable) {
			$this->mRenderable->renderHTML($Request, $Attr, $Parent);
		} else {
			$ResponseRenderer = new ResponseRenderer($this);
			$ResponseRenderer->renderHTML($Request, $Attr, $this);
		}
	}
}