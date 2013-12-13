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

    const FLAG_ROW_IS_HEADER = 0x01;
    const FLAG_ROW_IS_FOOTER = 0x02;
    const FLAG_ROW_FIRST_DATA_IS_LABEL = 0x04;

    const FLAG_DATA_IS_LABEL = 0x10;
    const CHECK_FLAG_DATA_IS_LABEL = 0x13; // FLAG_ROW_IS_HEADER + FLAG_ROW_IS_FOOTER + FLAG_DATA_IS_LABEL;

    /**
     * Render the start of a table.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $captionText text that should appear in the table caption
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderTableStart(IRequest $Request, $captionText = NULL, $class=null, $attr=null);

    /**
     * Render the start of a table row.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $flags ::FLAG_ROW_IS_HEADER, ::FLAG_ROW_IS_FOOTER, FLAG_ROW_FIRST_DATA_IS_LABEL
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderTableRowStart(IRequest $Request, $class=null, $flags=0, $attr=null);

    /**
     * Render the start of a table data element.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $span set span attribute
     * @param String|Array|NULL $class element classes
     * @param int $flags ::FLAG_DATA_IS_LABEL
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderTableDataStart(IRequest $Request, $span=0, $class=null, $flags=0, $attr=null);

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