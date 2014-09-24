<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Render\HTML;

use CPath\Response\IResponse;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType\MimeType;

final class HTMLMimeType extends MimeType
{
    public function __construct($typeName='text/html', IRequestedMimeType $nextMimeType=null) {
        parent::__construct($typeName, $nextMimeType);
    }
}
