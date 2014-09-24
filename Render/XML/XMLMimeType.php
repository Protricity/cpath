<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Render\XML;

use CPath\Response\IResponse;
use CPath\Request\IRequest;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\MimeType;

class XMLMimeType extends MimeType
{
    public function __construct($typeName='application/xml', IRequestedMimeType $nextMimeType=null) {
        parent::__construct($typeName, $nextMimeType);
    }

    /**
     * Send response headers for this mime type
     * @param int $code HTTP response code
     * @param string $message response message
     * @internal param \CPath\Request\IRequest $Request
     * @internal param string $origin Access-Control-Allow-Origin. Send false to disable
     * @return bool returns true if the headers were sent, false otherwise
     */
    function sendHeaders($code = 200, $message = 'OK') {
        if(!parent::sendHeaders($code, $message))
            return false;

        header('Access-Control-Allow-Origin: *');

        return true;
    }

}
