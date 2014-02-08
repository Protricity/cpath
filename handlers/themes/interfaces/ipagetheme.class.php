<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;

use CPath\Handlers\Interfaces\IAttributes;
use CPath\Framework\Request\Interfaces\IRequest;

interface IPageTheme {

    /**
     * Render the start of an html <body>.
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderBodyStart(IRequest $Request, IAttributes $Attr=NULL);

    /**
     * Render the end of an html <body>.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderBodyEnd(IRequest $Request);

    /**
     * Render the start of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderSectionStart(IRequest $Request, IAttributes $Attr=NULL);

    /**
     * Render the end of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|Null $className optional class name for this section
     * @return void
     */
    function renderSectionEnd(IRequest $Request, $className=NULL);


}