<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Fragments;


use CPath\Framework\Request\Interfaces\IRequest;

interface IRenderFragmentContent {

    /**
     * Render the fragment content
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderFragmentContent(IRequest $Request);
}