<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Request\MimeType;

interface IRequestedMimeType
{
    /**
     * Get the Mime type as a string
     * @return String
     */
    function getName();

    /**
     * Get next available mimetype or null if no more mimetypes were requested
     * @return IRequestedMimeType
     */
    function getNextMimeType();

    /**
     * Send response headers for this mime type
     * @param int $code HTTP response code
     * @param String $message response message
     * @internal param \CPath\Request\IRequest $Request
     * @return bool returns true if the headers were sent, false otherwise
     */
    //function sendHeaders($code = 200, $message = 'OK');
}