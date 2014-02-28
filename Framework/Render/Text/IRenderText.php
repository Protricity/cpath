<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render\Text;


use CPath\Framework\Request\Interfaces\IRequest;

interface IRenderText {

    /**
     * Render request as plain text
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    function renderText(IRequest $Request);
}