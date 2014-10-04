<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/4/14
 * Time: 5:28 PM
 */
namespace CPath\Render\HTML;

use CPath\Request\IRequest;

interface IHTMLTemplate
{
    /**
     * Add HTML Container Content
     * @param IRenderHTML $Content
     * @return void
     */
    //function addContent(IRenderHTML $Content);

    /**
     * Remove an IRenderHTML instance from the container
     * @param IRenderHTML $Content
     * @return bool true if the content was found and removed
     */
    //function removeContent(IRenderHTML $Content);

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IRenderHTML $Content
     * @return String|void always returns void
     */
    function renderHTMLContent(IRequest $Request, IRenderHTML $Content);
}