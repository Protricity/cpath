<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Render;


use CPath\Framework\Request\Interfaces\IRequest;

interface IRenderAggregate {
    /**
     * Return an instance of IRender
     * @param IRequest $Request the IRequest instance for this render
     //* @param String $path the matched request path for this destination
     //* @param String[] $args the arguments appended to the path
     * @return IRender return the renderer instance
     */
    function getRenderer(IRequest $Request);
}

