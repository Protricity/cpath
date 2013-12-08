<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Themes\Util;

use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;


class TableThemeUtil {
    private $mTheme, $mRequest, $mLastElm='none';

    public function __construct(IRequest $Request, ITableTheme $Theme) {
        $this->mRequest = $Request;
        $this->mTheme = $Theme;
    }

    /**
     * @param String|Callable $content
     * @param int $span
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     */
    public function renderTD($content, $span=0, $class=null, $attr=null) {
        $this->renderDataStart($span, $class, $attr);
        if($content)
            echo RI::ni(), is_callable($content) ? call_user_func($content) : $content;
        $this->renderDataEnd();
    }

    public function renderTR(Array $rowContent, $rowFlags=0) {
        switch($this->mLastElm) {
            case 'table':
                $this->renderRowStart($rowFlags);
                break;
            case 'tr':
                $this->renderRowEnd();
                $this->renderRowStart($rowFlags);
                break;
            case 'td':
                $this->renderDataEnd();
                $this->renderRowEnd();
                $this->renderRowStart($rowFlags);
                break;
            default:
                $this->renderStart();
                $this->renderRowStart();
        }
        foreach($rowContent as $content) {
            $this->renderDataStart();
            echo RI::ni(), $content;
            $this->renderDataEnd();
        }
        $this->renderRowEnd();
    }

    public function renderKeyPairsTable(Array $keyPairs, $keyTitle, $valueTitle, $captionText=null) {
        $this->renderStart($captionText);
        $this->renderTR(array($keyTitle, $valueTitle), true);
        foreach($keyPairs as $key=>$value) {
            $this->renderTR(array($key, $value), false);
        }
        $this->renderEnd();
    }

    /**
     * Render the start of a table header row.
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderHeaderStart($class = null, $attr = null) {
        $this->renderRowStart(ITableTheme::FLAG_ROW_IS_HEADER, $class, $attr);
    }

    /**
     * Render the start of a table footer row.
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderFooterStart($class = null, $attr = null) {
        $this->renderRowStart(ITableTheme::FLAG_ROW_IS_FOOTER, $class, $attr);
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
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderStart($captionText = NULL, $class = null, $attr = null) {
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
        $this->mTheme->renderTableStart($this->mRequest, $captionText, $class, $attr);
        $this->mLastElm = 'table';
    }

    /**
     * Render the start of a table row.
     * @param int $flags ::FLAG_ROW_IS_HEADER, ::FLAG_ROW_IS_FOOTER
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderRowStart($flags = 0, $class = null, $attr = null) {
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
        $this->mTheme->renderTableRowStart($this->mRequest, $flags, $class, $attr);
        $this->mLastElm = 'tr';
    }

    /**
     * Render the start of a table data element.
     * @param int $span set span attribute
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderDataStart($span = 0, $class = null, $attr = null) {
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
        $this->mTheme->renderTableDataStart($this->mRequest, $span, $class, $attr);
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
        $this->mTheme->renderTableDataEnd($this->mRequest);
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
        $this->mTheme->renderTableRowEnd($this->mRequest);
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
        $this->mTheme->renderTableEnd($this->mRequest, $footerText);
        $this->mLastElm = 'none';
    }


}