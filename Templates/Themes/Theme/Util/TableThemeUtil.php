<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Render\HTML\Theme\Util;

use CPath\Render\HTML\Attribute\Attr;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;
use CPath\Render\HTML\Theme\ITableTheme;


class TableThemeUtil {
    private $mTheme, $mLastElm='none', $mRowFlags=0, $mColNum=0, $mColMax=0;

    public function __construct(ITableTheme $Theme) {
        $this->mTheme = $Theme;
    }

    public function getTheme() {
        return $this->mTheme;
    }
    /**
     * @param String|Callable $content
     * @param String|IAttributes|Null $Attr optional attributes for this element
     * @param int $flags ::FLAG_DATA_IS_LABEL
     * @param int $span
     */
    public function renderTD($content, $Attr=null, $span=0, $flags=0) {
        $this->renderDataStart($Attr, $span, $flags);
        $this->renderContent($content);
        $this->renderDataEnd();
    }

    public function renderTR(Array $rowContent, $Attr=null, $flags=0, $RowAttr=null) {
        switch($this->mLastElm) {
            case 'table':
                $this->renderRowStart($flags, $Attr);
                break;
            case 'tr':
                $this->renderRowEnd();
                $this->renderRowStart($flags, $Attr);
                break;
            case 'td':
                $this->renderDataEnd();
                $this->renderRowEnd();
                $this->renderRowStart($flags, $Attr);
                break;
            default:
                $this->renderStart();
                $this->renderRowStart($flags, $Attr);
        }
        $i=0;
        foreach($rowContent as $content) {
            if($i==0 && $flags & ITableTheme::FLAG_ROW_FIRST_DATA_IS_LABEL)
                $this->renderDataStart($RowAttr, 0, $flags | ITableTheme::FLAG_DATA_IS_LABEL);
            else
                $this->renderDataStart($RowAttr, 0, $flags);
            $this->renderContent($content);
            $this->renderDataEnd();
            $i++;
        }
        $this->renderRowEnd();
    }

    private function renderContent ($content) {
        echo RI::ni(), !is_string($content) && is_callable($content) ? call_user_func($content) : ($content === null ? 'null' : $content);
    }

    public function renderKeyPairsTable(Array $keyPairs, $keyTitle, $valueTitle, $captionText=null) {
        $this->renderStart($captionText);
        $this->renderTR(array($keyTitle, $valueTitle), null, ITableTheme::FLAG_ROW_IS_HEADER);
        foreach($keyPairs as $key=>$value)
            $this->renderTR(array($key, $value), null, ITableTheme::FLAG_ROW_FIRST_DATA_IS_LABEL);

        $this->renderEnd();
    }

    /**
     * Render the start of a table header row.
     * @param String|IAttributes|Null $Attr optional attributes for this element
     * @return void
     */
    function renderHeaderStart($Attr = null) {
        $this->renderRowStart(ITableTheme::FLAG_ROW_IS_HEADER, $Attr);
    }

    /**
     * Render the start of a table footer row.
     * @param String|\CPath\Render\HTML\Attribute\IAttributes|Null $Attr optional attributes for this element
     * @return void
     */
    function renderFooterStart($Attr = null) {
        $this->renderRowStart(ITableTheme::FLAG_ROW_IS_FOOTER, $Attr);
    }

//    /**
//     * Render the end of a table header row.
//     * @return void
//     */
//    function renderHeaderEnd() {
//        $this->renderRowEnd();
//    }


    // ITableTheme

    /**
     * Render the start of a table.
     * @param String|NULL $captionText text that should appear in the table caption
     * @param String|\CPath\Render\HTML\Attribute\IAttributes|Null $Attr optional attributes for this element
     * @return void
     */
    function renderStart($captionText = NULL, $Attr=null) {
        $Attr = Attr::fromClass($Attr);
        switch($this->mLastElm) {
            case 'table':
                $this->renderEnd();
                break;
            case 'tr':
                $this->renderRowEnd();
                $this->renderEnd();
                break;
            case 'td':
                $this->renderDataEnd();
                $this->renderRowEnd();
                $this->renderEnd();
                break;
            case 'none': break;
        }
        $this->mTheme->renderTableStart($captionText, $Attr);
        $this->mLastElm = 'table';
    }

    /**
     * Render the start of a table row.
     * @param int $flags ::FLAG_ROW_IS_HEADER, ::FLAG_ROW_IS_FOOTER
     * @param String|\CPath\Render\HTML\Attribute\IAttributes|Null $Attr optional attributes for this element
     * @return void
     */
    function renderRowStart($flags = 0, $Attr = null) {
        $Attr = Attr::fromClass($Attr);
        switch($this->mLastElm) {
            case 'table': break;
            case 'tr':
                $this->renderRowEnd();
                break;
            case 'td':
                $this->renderDataEnd();
                $this->renderRowEnd();
                break;
            case 'none':
                $this->renderStart();
                break;
        }
        $this->mRowFlags = $flags;
        if($this->mColNum > $this->mColMax)
            $this->mColMax = $this->mColNum;
        $this->mColNum = 0;
        $this->mTheme->renderTableRowStart($flags, $Attr);
        $this->mLastElm = 'tr';
    }

    /**
     * Render the start of a table data element.
     * @param int $span set span attribute
     * @param int $flags ::FLAG_DATA_IS_LABEL
     * @param String|\CPath\Render\HTML\Attribute\IAttributes|Null $Attr optional attributes for this element
     * @return void
     */
    function renderDataStart($Attr=null, $span = 0, $flags = 0) {
        $Attr = Attr::fromClass($Attr);
        switch($this->mLastElm) {
            case 'table':
                $this->renderRowStart();
                break;
            case 'tr': break;
            case 'td':
                $this->renderRowEnd();
                break;
            case 'none':
                $this->renderStart();
                $this->renderRowStart();
                break;
        }
        if(is_string($span))
            switch($span) {
                case 'end':
                    $span = $this->mColMax - $this->mColNum;
                    if($span < 0)
                        $span = 0;
                    break;
            }
        $this->mColNum++;
        $this->mTheme->renderTableDataStart($span, $flags | $this->mRowFlags, $Attr);
        $this->mLastElm = 'td';
    }

    /**
     * Render the end of a table data element.
     * @return void
     */
    function renderDataEnd() {
        switch($this->mLastElm) {
            case 'table':
                $this->renderRowStart();
                $this->renderDataStart();
                break;
            case 'tr':
                $this->renderDataStart();
                break;
            case 'td': break;
            case 'none':
                $this->renderStart();
                $this->renderRowStart();
                $this->renderDataStart();
                break;
        }
        $this->mTheme->renderTableDataEnd();
        $this->mLastElm = 'tr';
    }

    /**
     * Render the start of a table data element.
     * @return void
     */
    function renderRowEnd() {
        switch($this->mLastElm) {
            case 'table':
                $this->renderRowStart();
                break;
            case 'tr': break;
            case 'td':
                $this->renderDataEnd();
                break;
            case 'none':
                $this->renderStart();
                $this->renderRowStart();
                break;
        }
        $this->mTheme->renderTableRowEnd();
        $this->mRowFlags = 0;
        $this->mLastElm = 'table';
    }



    /**
     * Render the end of a table.
     * @param String|NULL $footerText text that should appear in the footer
     * @return void
     */
    function renderEnd($footerText = NULL) {
        switch($this->mLastElm) {
            case 'table': break;
            case 'tr':
                $this->renderRowEnd();
                break;
            case 'td':
                $this->renderDataEnd();
                $this->renderRowEnd();
                break;
            case 'none':
                $this->renderStart();
                break;
        }
        $this->mTheme->renderTableEnd($footerText);
        $this->mLastElm = 'none';
    }


}