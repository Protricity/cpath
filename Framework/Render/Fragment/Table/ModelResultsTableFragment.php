<?php
namespace CPath\Framework\Render\Fragment\Table;

use CPath\Base;
use CPath\Data\Map\IKeyMap;
use CPath\Framework\Data\Map\Common\ArrayMap;
use CPath\Framework\PDO\Query\PDOSelectStats;
use CPath\Framework\PDO\Response\PDOSearchResponse;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Common\WebRequest;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\Theme\ITableTheme;
use CPath\Render\HTML\Theme\Util\TableThemeUtil;
use CPath\Request\IRequest;
use CPath\Templates\Themes\CPathDefaultTheme;

class ModelResultsTableFragment implements IRenderHTML, IHTMLSupportHeaders{

    private $mTheme;

    /**
     * @param \CPath\Render\HTML\Theme\ITableTheme $Theme
     */
    public function __construct(ITableTheme $Theme = null) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Write all support headers used by this IView instance
     * @param \CPath\Framework\Render\Header\Interfaces\IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IHeaderWriter $Head) {
        $Head->writeStyleSheet(__NAMESPACE__ . '\assets\modelresultstablefragment.css');
        $Head->writeScript(__NAMESPACE__ . '\assets\modelresultstablefragment.js');
    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @param \CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @param \CPath\Framework\PDO\Response\PDOSearchResponse $Response
     * @return void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr=null, PDOSearchResponse $Response = NULL)
    {
        $Table = new TableThemeUtil($Request, $this->mTheme);

        $Query = $Response->getQuery();
        $Stats = $Query->getDescriptor()->execFullStats();
        $json = ArrayMap::get($Stats);
        $json = json_encode($json);
        $Attr->add('data-stats', $json);
        //$attr = "data-stats='{$json}'" . ($attr ? ' ' . $attr : '');

        $Table->renderStart($Response, $Attr);
        $Table->renderHeaderStart();

        $row = $Query->fetch();
        if($row) {
            if($row instanceof IKeyMap)
                $row = ArrayMap::get($row);


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
                if($row instanceof IKeyMap)
                    $row = ArrayMap::get($row);
            }
        }

        $this->renderFooterLinks($Request, $Table, $Stats);

        $Table->renderEnd();
    }

    function renderFooterLinks(IRequest $Request, TableThemeUtil $Table, PDOSelectStats $Stats, $pageLinks=15) {
        //$Stats = new PDOSelectStats(800, 35, 258);
        $Table->renderFooterStart();
        $url = substr($Request->getPath(), 1);
        if($Request instanceof WebRequest)
            $url = $Request->getURL();

        $Table->renderTD(function() use ($Stats, $pageLinks, $url) {
            //echo RI::ni(), "<a href='", $url, $Stats->getURL(1), "' class='search-form-page-first'>First</a>";
            //echo RI::ni(), "<a href='". $url. $Stats->getURL($Stats->getTotalPages()). "' class='search-form-page-last'>Last</a>";
            echo RI::ni(),"<a", (($p = $Stats->getPreviousPage()) ? " href='". $url. $Stats->getURL($p). "'" : ''), " class='search-form-page search-form-page-previous'>Previous</a>";
                echo RI::ni(), "<div class='search-form-pages'>";// data-template-url='" .  $url . $Stats->getURL('%PAGE%', '%LIMIT%') . "'>";
                foreach($Stats->getPageIDs() as $id) {
                    echo RI::ni(1), "<a href='", $url, $Stats->getURL($id), "' class='search-form-page'>{$id}</a>";
                }
                echo RI::ni(), "</div>";
            echo RI::ni(),"<a", (($p = $Stats->getNextPage()) ? " href='". $url. $Stats->getURL($p). "'" : ''), " class='search-form-page search-form-page-next'>Next</a>";
        }, 'search-form-controls', 'end');

    }

}
