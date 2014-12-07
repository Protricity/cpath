<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 4:50 PM
 */
namespace CPath\Response;

use CPath\Request\IRequest;

interface IResponseHeaders
{
    /**
     * Send response headers for this response
     * @param IRequest $Request
     * @param string $mimeType
     * @return bool returns true if the headers were sent, false otherwise
     */
    function sendHeaders(IRequest $Request, $mimeType = null);

	/**
	 * Set redirect header for response object
	 * @param string $uri
	 * @param int $timeout in seconds
	 * @return $this
	 */
	//function setRedirect($uri, $timeout=null);

	/**
	 * Set redirect header for response object
	 * @param string $name
	 * @param string|null $value
	 * @return $this
	 */
	//function addHeader($name, $value=null);
}