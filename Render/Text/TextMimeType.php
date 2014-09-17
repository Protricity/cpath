<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Render\Text;

use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Request\MimeType\IRequestedMimeType;

final class TextMimeType implements IRequestedMimeType
{
    private $mTypeName;

    public function __construct($typeName='text/plain') {
        $this->mTypeName = $typeName;
    }

    /**
     * Get the Mime type as a string
     * @return String
     */
    function getMimeTypeName() {
        return $this->mTypeName;
    }

    /**
     * Send response headers for this mime type
     * @param IResponse $Response
     * @throws \Exception
     * @return void
     */
    function sendHeaders(IResponse $Response) {
        if (headers_sent())
            throw new \Exception("Headers were already sent");

        header("HTTP/1.1 " . $Response->getCode() . " " . preg_replace('/[^\w -]/', '', $Response->getMessage()));
        header("Content-Type: " . $this->mTypeName);

        header('Access-Control-Allow-Origin: *');
    }
}