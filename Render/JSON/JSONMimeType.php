<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Render\JSON;

use CPath\Response\IResponse;
use CPath\Request\IRequest;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\MimeType;

class JSONMimeType extends MimeType
{
    public function __construct($typeName='application/json', IRequestedMimeType $nextMimeType=null) {
        parent::__construct($typeName, $nextMimeType);
    }


}