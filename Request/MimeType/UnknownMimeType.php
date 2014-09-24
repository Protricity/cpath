<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Request\MimeType;

use CPath\Response\IResponse;

final class UnknownMimeType implements IRequestedMimeType
{
    private $mTypeName;

    public function __construct($typeName) {
        $this->mTypeName = $typeName;
    }

    /**
     * Get the Mime type as a string
     * @return String
     */
    function getName() {
        return $this->mTypeName;
    }


    /**
     * Send response headers for this mime type
     * @param \CPath\Response\IResponse|int $code
     * @param string $message
     * @throws \Exception
     * @internal param \CPath\Request\MimeType\IRequest $Request
     * @return void
     */
    function sendHeaders($code = 200, $message = 'OK') {
        if (headers_sent())
            throw new \Exception("Headers were already sent");

        header("HTTP/1.1 " . $code->getCode() . " " . preg_replace('/[^\w -]/', '', $code->getMessage()));
        header("Content-Type: " . $this->mTypeName);

        header('Access-Control-Allow-Origin: *');
    }
}