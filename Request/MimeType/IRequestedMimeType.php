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
    function getMimeTypeName();
}