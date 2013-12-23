<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;


interface IHandler {
    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request);
}