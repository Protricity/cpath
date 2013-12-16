<?php
namespace CPath\Handlers\Fragments;

use CPath\Handlers\Themes\CPathDefaultTheme;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Util\TableThemeUtil;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;
use CPath\Model\DB\PDOModel;
use CPath\Model\DB\PDOSelectStats;
use CPath\Model\DB\SearchResponse;
use CPath\Request\Web;

class ModelResultsTableFragment implements IHandler{

    private $mResponse, $mTheme;

    /**
     * @param SearchResponse $Response
     * @param ITableTheme $Theme
     */
    public function __construct(SearchResponse $Response, ITableTheme $Theme = null) {
        $this->mResponse = $Response;
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function render(IRequest $Request, $class = NULL, $attr = NULL)
    {
        $Table = new TableThemeUtil($Request, $this->mTheme);

        $Query = $this->mResponse->getQuery();
        $Stats = $Query->getDescriptor()->execFullStats();

        $Table->renderStart($this->mResponse, $class, $attr);
        $Table->renderHeaderStart();

        $row = $Query->fetch();
        if($row) {
            if($row instanceof PDOModel)
                $row = $row->exportData();

            if($Query->hasDescriptor()) {
                $Descriptor = $Query->getDescriptor();
                foreach($row as $key=>$value)
                    $Table->renderTD($Descriptor->getColumn($key)->getTitle());
            } else {
                foreach($row as $key=>$value)
                    $Table->renderTD($key);
            }

            while($row) {
                $Table->renderRowStart();
                foreach($row as $value)
                    $Table->renderTD($value);
                $row = $Query->fetch();
                if($row instanceof PDOModel)
                    $row = $row->exportData();
            }
        }

        $this->renderFooterLinks($Request, $Table, $Stats);

        $Table->renderEnd();
    }

    function renderFooterLinks(IRequest $Request, TableThemeUtil $Table, PDOSelectStats $Stats, $pageLinks=15) {
        //$Stats = new PDOSelectStats(800, 35, 258);
        $Table->renderFooterStart();
        $url = substr($Request->getPath(), 1);
        if($Request instanceof Web)
            $url = $Request->getURL();

        $Table->renderTD(function() use ($Stats, $pageLinks, $url) {
            //echo RI::ni(), "<a href='", $url, $Stats->getURL(1), "' class='search-form-page-first'>First</a>";
            //echo RI::ni(), "<a href='". $url. $Stats->getURL($Stats->getTotalPages()). "' class='search-form-page-last'>Last</a>";
            echo RI::ni(),"<a", (($p = $Stats->getPreviousPage()) ? " href='". $url. $Stats->getURL($p). "'" : ''), " class='search-form-page search-form-page-previous'>Previous</a>";
                echo RI::ni(), "<div class='search-form-pages' data-template-url='" .  $url . $Stats->getURL('%PAGE%', '%LIMIT%') . "'>";
                foreach($Stats->getPageIDs() as $i=>$id) {
                    echo RI::ni(1), "<a href='", $url, $Stats->getURL($id), "' class='search-form-page'>{$id}</a>";
                }
                echo RI::ni(), "</div>";
            echo RI::ni(),"<a", (($p = $Stats->getNextPage()) ? " href='". $url. $Stats->getURL($p). "'" : ''), " class='search-form-page search-form-page-next'>Next</a>";
        }, 'search-form-controls', 'end');

    }
}
