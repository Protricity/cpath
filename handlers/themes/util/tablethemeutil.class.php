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
    private $mTheme, $mLastElm='none';

    public function __construct(ITableTheme $Theme) {
        $this->mTheme = $Theme;
    }

    public function renderStart(IRequest $Request, $captionText=null) {
        $this->mTheme->renderTableStart($Request, $captionText);
        $this->mLastElm = 'table';
    }

    public function renderEnd(IRequest $Request) {
        switch($this->mLastElm) {
            case 'table':
                $this->mTheme->renderTableEnd($Request);
                break;
            case 'tr':
                $this->mTheme->renderTableRowEnd($Request);
                $this->mTheme->renderTableEnd($Request);
                break;
            case 'td':
                $this->mTheme->renderTableDataEnd($Request);
                $this->mTheme->renderTableRowEnd($Request);
                $this->mTheme->renderTableEnd($Request);
                break;
            case 'done':
            case 'none':
                break;
        }
        $this->mLastElm = 'done';
    }

    public function renderTD(IRequest $Request, $content, $span=0) {
        switch($this->mLastElm) {
            case 'table':
                $this->mTheme->renderTableRowStart($Request);
                break;
            case 'tr': break;
            case 'td': break;
            case 'none':
                $this->mTheme->renderTableStart($Request);
                $this->mTheme->renderTableRowStart($Request);
                break;
        }
        $this->mTheme->renderTableDataStart($Request, $span);
        echo RI::ni(), $content;
        $this->mTheme->renderTableDataEnd($Request);
        $this->mLastElm = 'tr';
    }

    public function renderTR(IRequest $Request, Array $rowContent, $isHeader=false) {
        switch($this->mLastElm) {
            case 'table':
                $this->mTheme->renderTableRowStart($Request);
                break;
            case 'tr':
                $this->mTheme->renderTableRowEnd($Request);
                $this->mTheme->renderTableRowStart($Request, $isHeader);
                break;
            case 'td':
                $this->mTheme->renderTableDataEnd($Request);
                $this->mTheme->renderTableRowEnd($Request);
                $this->mTheme->renderTableRowStart($Request, $isHeader);
                break;
            default:
                $this->mTheme->renderTableStart($Request);
                $this->mTheme->renderTableRowStart($Request);
        }
        foreach($rowContent as $content) {
            $this->mTheme->renderTableDataStart($Request);
            echo RI::ni(), $content;
            $this->mTheme->renderTableDataEnd($Request);
        }
        $this->mTheme->renderTableRowEnd($Request);
        $this->mLastElm = 'table';
    }

    public function renderKeyPairsTable(IRequest $Request, Array $keyPairs, $keyTitle, $valueTitle, $captionText=null) {
        $this->renderStart($Request, $captionText);
        $this->renderTR($Request, array($keyTitle, $valueTitle), true);
        foreach($keyPairs as $key=>$value) {
            $this->renderTR($Request, array($key, $value), false);
        }
        $this->renderEnd($Request);
    }
}