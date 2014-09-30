<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 4:12 PM
 */
namespace CPath\Request\Exceptions;

use CPath\Request\IRequest;
use CPath\Request\RequestException;
use CPath\Request\Web\WebRequest;
use CPath\Response\IResponseCode;

class RedirectException extends RequestException
{
    private $mPath;
    private $mDelay;

    /**
     * @param string $message
     * @param null $newPath
     * @param int $delay
     */
    function __construct($message, $newPath, $delay=5) {
        parent::__construct($message, IResponseCode::STATUS_TEMPORARY_REDIRECT);
        $this->mPath = $newPath;
        $this->mDelay = $delay;
    }
    /**
     * Return the redirection url
     * @return String
     */
    function getLocationURL() {
        return $this->mPath;
    }


    /**
     * Send response headers for this response
     * @param IRequest $Request
     * @param string $mimeType
     * @return bool returns true if the headers were sent, false otherwise
     */
    function sendHeaders(IRequest $Request, $mimeType = null) {
        if(!parent::sendHeaders($Request, $mimeType))
            return false;

        $url = $Request->getDomainPath(false) . $this->getLocationURL();

        if($this->mDelay) {
            header( "Refresh: " . $this->mDelay . "; URL=" . $url );
//            setTimeout(function () {
//                window.location.href= 'http://www.google.com'; // the redirect goes here
//
//            },5000); // 5 seconds
        } else {
            header("Location: " . $url);
        }

        return true;
    }
}