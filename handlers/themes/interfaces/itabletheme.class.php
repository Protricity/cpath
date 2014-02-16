<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Interfaces;

use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Interfaces\IAttributes;

interface ITableTheme {

    const FLAG_ROW_IS_HEADER = 0x01;
    const FLAG_ROW_IS_FOOTER = 0x02;
    const FLAG_ROW_FIRST_DATA_IS_LABEL = 0x04;

    const FLAG_DATA_IS_LABEL = 0x10;
    const CHECK_FLAG_DATA_IS_LABEL = 0x13; // FLAG_ROW_IS_HEADER + FLAG_ROW_IS_FOOTER + FLAG_DATA_IS_LABEL;

    /**
     * Render the start of a table.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $captionText text that should appear in the table caption
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableStart(IRequest $Request, $captionText = NULL, IAttributes $Attr=null);

    /**
     * Render the start of a table row.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $flags ::FLAG_ROW_IS_HEADER, ::FLAG_ROW_IS_FOOTER, FLAG_ROW_FIRST_DATA_IS_LABEL
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableRowStart(IRequest $Request, $flags=0, IAttributes $Attr=null);

    /**
     * Render the start of a table data element.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $span set span attribute
     * @param int $flags ::FLAG_DATA_IS_LABEL
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableDataStart(IRequest $Request, $span=0, $flags=0, IAttributes $Attr=null);

    /**
     * Render the end of a table data element.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableDataEnd(IRequest $Request);

    /**
     * Render the end of a table row.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableRowEnd(IRequest $Request);

    /**
     * Render the end of a table.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $footerText text that should appear in the footer
     * @return void
     */
    function renderTableEnd(IRequest $Request, $footerText=NULL);
}