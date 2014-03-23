<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Route\Render;


use CPath\Framework\Request\Interfaces\IRequest;
use String;

interface IDestination {
    /**
     * Render this route destination
     * @param IRequest $Request the IRequest instance for this render
     * @param String $path the matched request path for this destination
     * @param String[] $args the arguments appended to the path
     * @return String|void always returns void
     */
    function renderDestination(IRequest $Request, $path, $args);
}

