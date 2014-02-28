<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render\HTML;

use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Request\Interfaces\IRequest;

interface IRenderHTML {

    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Framework\Render\Attribute\IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr=null);
}