<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;

use CPath\Interfaces\IRequest;

interface ITableTheme extends ITheme {

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $headerText text that should appear in the footer
     * @return void
     */
    function renderTableStart(IRequest $Request, $headerText=NULL);

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableColumnStart(IRequest $Request);

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableRowStart(IRequest $Request);

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableRowEnd(IRequest $Request);

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableColumnEnd(IRequest $Request);

    /**
     * Render the end of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $footerText text that should appear in the footer
     * @return void
     */
    function renderTableEnd(IRequest $Request, $footerText=NULL);
}