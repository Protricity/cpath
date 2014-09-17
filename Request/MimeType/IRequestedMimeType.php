<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Request\MimeType;

use CPath\Framework\Response\Interfaces\IResponse;

interface IRequestedMimeType
{
    /**
     * Get the Mime type as a string
     * @return String
     */
    function getMimeTypeName();

    /**
     * Send response headers for this mime type
     * @param IResponse $Response
     * @return void
     */
    function sendHeaders(IResponse $Response);
}