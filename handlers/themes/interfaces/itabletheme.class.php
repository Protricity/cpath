<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;

use CPath\Interfaces\IRequest;

interface ITableTheme {

    /**
     * Render the start of a table.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $captionText text that should appear in the table caption
     * @return void
     */
    function renderTableStart(IRequest $Request, $captionText = NULL);

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param bool $isHeader set true if this row is a <th>
     * @return void
     */
    function renderTableRowStart(IRequest $Request, $isHeader=false);

    /**
     * Render the start of a table data element.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $span set span attribute
     * @return void
     */
    function renderTableDataStart(IRequest $Request, $span=0);

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableDataEnd(IRequest $Request);

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableRowEnd(IRequest $Request);

    /**
     * Render the end of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $footerText text that should appear in the footer
     * @return void
     */
    function renderTableEnd(IRequest $Request, $footerText=NULL);
}