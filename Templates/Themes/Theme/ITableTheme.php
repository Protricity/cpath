<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Theme;

use CPath\Render\HTML\Attribute\IAttributes;

interface ITableTheme {

    const FLAG_ROW_IS_HEADER = 0x01;
    const FLAG_ROW_IS_FOOTER = 0x02;
    const FLAG_ROW_FIRST_DATA_IS_LABEL = 0x04;

    const FLAG_DATA_IS_LABEL = 0x10;
    const CHECK_FLAG_DATA_IS_LABEL = 0x13; // FLAG_ROW_IS_HEADER + FLAG_ROW_IS_FOOTER + FLAG_DATA_IS_LABEL;

    /**
     * Render the start of a table.
     * @param String|NULL $captionText text that should appear in the table caption
     * @param \CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableStart($captionText = NULL, IAttributes $Attr=null);

    /**
     * Render the start of a table row.
     * @param int $flags ::FLAG_ROW_IS_HEADER, ::FLAG_ROW_IS_FOOTER, FLAG_ROW_FIRST_DATA_IS_LABEL
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableRowStart($flags=0, IAttributes $Attr=null);

    /**
     * Render the start of a table data element.
     * @param int $span set span attribute
     * @param int $flags ::FLAG_DATA_IS_LABEL
     * @param \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableDataStart($span=0, $flags=0, IAttributes $Attr=null);

    /**
     * Render the end of a table data element.
     * @return void
     */
    function renderTableDataEnd();

    /**
     * Render the end of a table row.
     * @return void
     */
    function renderTableRowEnd();

    /**
     * Render the end of a table.
     * @param String|NULL $footerText text that should appear in the footer
     * @return void
     */
    function renderTableEnd($footerText=NULL);
}