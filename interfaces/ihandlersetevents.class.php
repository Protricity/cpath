<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

use CPath\Framework\Request\Interfaces\IRequest;

interface IHandlerSetEvents {
    /**
     * Performed before an IHandlerSet renders a handler
     * @param IRequest $Request
     * @return void
     */
    function onRender(IRequest $Request);
}