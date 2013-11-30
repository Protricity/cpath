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

    public function renderStart($captionText=null) {
        $this->mTheme->renderTableStart($this->mRequest, $captionText);
        $this->mLastElm = 'table';
    }

    public function renderEnd() {
        switch($this->mLastElm) {
            case 'table':
                $this->mTheme->renderTableEnd($this->mRequest);
                break;
            case 'tr':
                $this->mTheme->renderTableRowEnd($this->mRequest);
                $this->mTheme->renderTableEnd($this->mRequest);
                break;
            case 'td':
                $this->mTheme->renderTableDataEnd($this->mRequest);
                $this->mTheme->renderTableRowEnd($this->mRequest);
                $this->mTheme->renderTableEnd($this->mRequest);
                break;
            case 'done':
            case 'none':
                break;
        }
        $this->mLastElm = 'done';
    }

    /**
     * @param String|Callable $content
     * @param int $span
     */
    public function renderTD($content, $span=0) {
        switch($this->mLastElm) {
            case 'table':
                $this->mTheme->renderTableRowStart($this->mRequest);
                break;
            case 'tr': break;
            case 'td': break;
            case 'none':
                $this->mTheme->renderTableStart($this->mRequest);
                $this->mTheme->renderTableRowStart($this->mRequest);
                break;
        }
        $this->mTheme->renderTableDataStart($this->mRequest, $span);
        echo RI::ni(), is_callable($content) ? call_user_func($content) : $content;
        $this->mTheme->renderTableDataEnd($this->mRequest);
        $this->mLastElm = 'tr';
    }

    public function renderTR(Array $rowContent, $isHeader=false) {
        switch($this->mLastElm) {
            case 'table':
                $this->mTheme->renderTableRowStart($this->mRequest, $isHeader);
                break;
            case 'tr':
                $this->mTheme->renderTableRowEnd($this->mRequest);
                $this->mTheme->renderTableRowStart($this->mRequest, $isHeader);
                break;
            case 'td':
                $this->mTheme->renderTableDataEnd($this->mRequest);
                $this->mTheme->renderTableRowEnd($this->mRequest);
                $this->mTheme->renderTableRowStart($this->mRequest, $isHeader);
                break;
            default:
                $this->mTheme->renderTableStart($this->mRequest);
                $this->mTheme->renderTableRowStart($this->mRequest);
        }
        foreach($rowContent as $content) {
            $this->mTheme->renderTableDataStart($this->mRequest);
            echo RI::ni(), $content;
            $this->mTheme->renderTableDataEnd($this->mRequest);
        }
        $this->mTheme->renderTableRowEnd($this->mRequest);
        $this->mLastElm = 'table';
    }

    public function renderKeyPairsTable(Array $keyPairs, $keyTitle, $valueTitle, $captionText=null) {
        $this->renderStart($captionText);
        $this->renderTR(array($keyTitle, $valueTitle), true);
        foreach($keyPairs as $key=>$value) {
            $this->renderTR(array($key, $value), false);
        }
        $this->renderEnd();
    }
}