<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Request\MimeType;

final class UnknownMimeType extends MimeType
{
    public function __construct($typeName, IRequestedMimeType $nextMimeType=null) {
        parent::__construct($typeName, $nextMimeType);
    }

}