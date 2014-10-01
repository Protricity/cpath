<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 12:47 PM
 */
namespace CPath\Render\HTML\URL;

interface IHasURL
{
    /**
     * Return the url for this object
     * @internal param \CPath\Request\IRequest $Request
     * @return String
     */
    function getURL();
}

