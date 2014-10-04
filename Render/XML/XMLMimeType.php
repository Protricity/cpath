<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Render\XML;

use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\MimeType;

class XMLMimeType extends MimeType
{
    public function __construct($typeName='application/xml', IRequestedMimeType $nextMimeType=null) {
        parent::__construct($typeName, $nextMimeType);
    }
}
