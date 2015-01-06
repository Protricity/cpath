<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;
use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLMimeType;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\IRenderAll;
use CPath\Render\JSON\IRenderJSON;
use CPath\Render\JSON\JSONMimeType;
use CPath\Render\Map\MapRenderer;
use CPath\Render\Text\IRenderText;
use CPath\Render\Text\TextMimeType;
use CPath\Render\XML\IRenderXML;
use CPath\Render\XML\XMLMimeType;
use CPath\Request\IRequest;
use CPath\Request\MimeType\UnknownMimeType;
use CPath\Response\Common\ExceptionResponse;
use CPath\Route\CPathMap;
use CPath\Route\IRoutable;
use CPath\Route\RouteBuilder;

final class ResponseRenderer implements IKeyMap, IRenderAll, IResponseHeaders, IHTMLSupportHeaders, IBuildable, IRoutable {
    private $mResponse;
    private $mSent = false;
    //private $mContainer;

	private $mHeaders = array();

	/**
	 * @param IResponse|\Exception $Response
	 */
	function __construct($Response) {
        $this->mResponse = $Response;
        //$this->mContainer = $HTMLContainer;
    }

	function getResponse() {
		$Response = $this->mResponse;
		if(($Response instanceof \Exception) && (!$Response instanceof IResponse)) {
			$Response = new ExceptionResponse($Response);
		} else if(!is_object($Response)) {
			$Response = new Response("Not a response object: " . gettype($Response), false);
		} else if(!$Response instanceof IResponse) {
			$Response = new Response("Invalid Response Object: " . get_class($Response), false);
		}
		return $Response;
	}

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode() {
		return $this->getResponse()->getCode();
	}

	/**
	 * Get the IResponse Message
	 * @return String
	 */
	function getMessage() {
		return $this->getResponse()->getMessage();
	}

    /**
     * Send response headers for this response
     * @param IRequest $Request
     * @param string $mimeType
     * @return bool returns true if the headers were sent, false otherwise
     */
    function sendHeaders(IRequest $Request, $mimeType = null) {
//	    if($this->mResponse instanceof IHeaderResponse)
//		    return $this->mResponse->sendHeaders($Request, $mimeType);

        if($this->mSent || headers_sent())
            return false;

        header("HTTP/1.1 " . $this->getCode() . " " . preg_replace('/[^\w -]/', '', $this->getMessage()));
        header("MainContent-Type: " . $mimeType);

	    foreach($this->mHeaders as $name => $value)
		    header($value === null ? $name : $name . ': ' . $value);

        $this->mSent = true;
        return true;
    }

	function setAccessControlAllowOrigin($allow = '*') {
		$this->addHeader('Access-Control-Allow-Origin', $allow);
		return $this;
	}

//	/**
//	 * Set redirect header for response object
//	 * @param \CPath\Request\IRequest $Request
//	 * @param string $uri
//	 * @param int $timeout in seconds
//	 * @return $this
//	 */
//	function setRedirect(IRequest $Request, $uri, $timeout = null) {
//		$domain = $Request->getDomainPath();
//		if(strpos($uri, $domain) !== 0)
//			$uri = $domain . $uri;
//
//		if($timeout === null) {
//			$this->addHeader('Location', $uri);
//
//		} else {
//			$this->addHeader('Refresh', $timeout . '; URL=' . $uri);
//
//		}
//		return $this;
//	}

	/**
	 * Add response headers to this response object
	 * @param String $name i.e. 'Location' or 'Location: /path'
	 * @param String|null $value i.e. '/path'
	 * @return $this
	 */
	function addHeader($name, $value=null) {
		$this->mHeaders[$name] = $value;
		return $this;
	}

	/**
	 * Map data to a data map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
    function mapKeys(IKeyMapper $Map) {
	    if($this->mResponse instanceof IKeyMap) {
		    $this->mResponse->mapKeys($Map);
	    } else {
		    $Map->map(IResponse::STR_MESSAGE, $this->getMessage());
		    $Map->map(IResponse::STR_CODE, $this->getCode());
	    }
    }

	/**
	 * Renders a response object or returns false
	 * @param IRequest $Request the IRequest inst for this render
	 * @param bool $sendHeaders if true, sends the response headers
	 * @return bool returns false if no rendering occurred
	 */
	function render(IRequest $Request, $sendHeaders=true) {
		if($sendHeaders)
			$this->sendHeaders($Request);

		$MimeType = $Request->getMimeType();

		if ($MimeType instanceof HTMLMimeType) {
			$this->renderHTML($Request);

		} elseif ($MimeType instanceof XMLMimeType) {
			$this->renderXML($Request);

		} elseif ($MimeType instanceof JSONMimeType) {
			$this->renderJSON($Request);

		} elseif ($MimeType instanceof TextMimeType) {
			$this->renderText($Request);

		} elseif ($MimeType instanceof UnknownMimeType) {
			return false;

		}

		return true;
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param \CPath\Render\HTML\Header\IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		if($this->mResponse instanceof IHTMLSupportHeaders)
			$this->mResponse->writeHeaders($Request, $Head);
	}

	/**
	 * Render request as html and sends headers as necessary
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
	 * @param IRenderHTML $Parent
	 * @return void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		//$this->sendHeaders('text/html');

		$Response = $this->mResponse;
		if($Response instanceof IRenderHTML && $Response !== $Parent) {
			$Response->renderHTML($Request, $Attr, $Parent);

		} else {
			$Mapper = new MapRenderer($this);
			$Mapper = $Mapper->getRenderer($Request);
			$Mapper->renderHTML($Request, $Attr, $Parent);
		}
	}

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as JSON
     * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @return void
     */
    function renderJSON(IRequest $Request) {
        $Response = $this->mResponse;
        if($Response instanceof IRenderJSON) {
            $Response->renderJSON($Request);

        } elseif($Response instanceof IKeyMap) {
	        $Mapper = new MapRenderer($this);
	        $Mapper = $Mapper->getRenderer($Request);
	        $Response->mapKeys($Mapper);

        } else {
	        $Mapper = new MapRenderer($this);
	        $Mapper = $Mapper->getRenderer($Request);
	        $Mapper->renderJSON($Request);
        }
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as XML
     * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @param string $rootElementName Optional name of the root element
     * @param bool $declaration
     * @return void
     */
    function renderXML(IRequest $Request, $rootElementName = 'root', $declaration = false) {
        $Response = $this->mResponse;
        if($Response instanceof IRenderXML) {
            $Response->renderXML($Request, $rootElementName, $declaration);

        } elseif($Response instanceof IKeyMap) {
	        $Mapper = new MapRenderer($this);
	        $Mapper = $Mapper->getRenderer($Request);
            $Response->mapKeys($Mapper);

        } else {
	        $Mapper = new MapRenderer($this);
	        $Mapper = $Mapper->getRenderer($Request);
	        $Mapper->renderXML($Request, $rootElementName, true);
        }
    }

    /**
     * Sends headers if necessary, executes the request, and renders an IResponse as plain text
     * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @return void
     */
    function renderText(IRequest $Request) {
        $Response = $this->mResponse;
        if($Response instanceof IRenderText) {
            $Response->renderText($Request);

        } else {
	        $Mapper = new MapRenderer($this);
	        $Mapper = $Mapper->getRenderer($Request);
	        $Mapper->renderText($Request);
        }
    }

	// Static

	/**
	 * Handle this request and render any content
	 * @param IBuildRequest $Request the build request inst for this build session
	 * @return void
	 * @build --disable 0
	 * Note: Use doctag 'build' with '--disable 1' to have this IBuildable class skipped during a build
	 */
	static function handleBuildStatic(IBuildRequest $Request) {
		$RouteBuilder = new RouteBuilder($Request, new CPathMap(), '_response');
		$RouteBuilder->writeRoute('ANY *', __CLASS__);
	}

	/**
	 * Route the request to this class object and return the object
	 * @param IRequest $Request the IRequest inst for this render
	 * @param array|null $Previous all previous response object that were passed from a handler, if any
	 * @param null|mixed $_arg [varargs] passed by route map
	 * @return void|bool|Object returns a response object
	 * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
	 * If false is returned, this static handler will be called again if another handler returns an object
	 * If an object is returned, it is passed along to the next handler
	 */
	static function routeRequestStatic(IRequest $Request, Array &$Previous = array(), $_arg = null) {
		if(sizeof($Previous) === 0
			|| !$Previous[0] instanceof IResponse) {
			return false;
		}

		$Object = $Previous[0];
		return new ResponseRenderer($Object);
	}
}

