<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\JSON;


use CPath\Request\IRequest;

interface IRenderJSON {

    /**
     * Render request as JSON
     * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and remaining args
     * @return String|void always returns void
     */
    function renderJSON(IRequest $Request);
}

