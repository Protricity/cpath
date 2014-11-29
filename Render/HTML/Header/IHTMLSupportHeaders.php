<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/31/14
 * Time: 2:25 PM
 */
namespace CPath\Render\HTML\Header;

use CPath\Request\IRequest;

interface IHTMLSupportHeaders
{

    /**
     * Write all support headers used by this renderer
     * @param IRequest $Request
     * @param IHeaderWriter $Head the writer inst to use
     * @return void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head);
}

