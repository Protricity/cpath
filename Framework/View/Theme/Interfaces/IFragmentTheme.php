<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\View\Theme\Interfaces;

use CPath\Describable\IDescribable;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Themes\Interfaces\IAttributes;

interface IFragmentTheme {

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param IDescribable|String|Null $Description optional fragment header text or description
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderFragmentStart(IRequest $Request, $Description=null, \CPath\Framework\Render\Attribute\IAttributes $Attr=null);

    /**
     * Render the end of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderFragmentEnd(IRequest $Request);
}