<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:46 PM
 */
namespace CPath\Request\MimeType;

final class XMLMimeType implements IRequestedMimeType
{
    private $mTypeName;

    public function __construct($typeName) {
        $this->mTypeName = $typeName;
    }

    /**
     * Get the Mime type as a string
     * @return String
     */
    function getMimeTypeName() {
        return $this->mTypeName;
    }
}