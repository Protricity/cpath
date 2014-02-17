<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;


use CPath\Framework\Api\Field\Field;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Api\Util\APIExecuteUtil;
use CPath\Framework\Api\Util\APIRenderUtil;
use CPath\Framework\PDO\Interfaces\IAPIGetBrowseCallbacks;
use CPath\Framework\PDO\Interfaces\IPDOModelSearchRender;
use CPath\Framework\PDO\Interfaces\IReadAccess;
use CPath\Framework\PDO\Interfaces\ISelectDescriptor;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Query\PDOSelectStats;
use CPath\Framework\PDO\Response\PDOSearchResponse;
use CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException;
use CPath\Framework\PDO\Table\Model\Query\PDOModelSelect;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Framework\Render\Interfaces\IAttributes;
use CPath\Framework\Render\Interfaces\IRenderHtml;
use CPath\Framework\Request\Interfaces\IRequest;

class API_GetBrowse extends API_Base implements IRenderHtml {

    private $mLimit, $mLimitMax;

    /**
     * Construct an instance of the GET API
     * @param PDOTable|IReadAccess $Table the PDOTable for this API
     * @param int $limit
     * @param int $limitMax
     * PRIMARY key is already included
     */
    function __construct(PDOTable $Table, $limit=25, $limitMax=100) {
        parent::__construct($Table);
        $this->mLimit = $limit;
        $this->mLimitMax = $limitMax;
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupFields() {
//        $this->addField('search', new RequiredParam("SEARCH for ".$Model::modelName()));
//        $this->addField('search_by', new Param("SEARCH by column. Allowed: [".implode(', ', array_keys($this->mColumns))."]"));
//        $this->addField('logic', new Field("The search logic to use [AND, OR]. Default=OR"));
        $this->addField(new Field('limit', "The number of rows to return. Max=".$this->mLimitMax));
        $this->addField(new Field('page', "The page number to return"));
    }


    /**
     * Get the Object Description
     * @return \CPath\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Browse for a " . $this->getTable()->getModelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return PDOSearchResponse the api call response with data, message, and status
     * @throws ModelNotFoundException if the Model was not found
     * @throws \Exception if no valid columns were found
     */
    final function execute(IRequest $Request) {

        $Util = new APIExecuteUtil($this);
        $Util->processRequest($Request);

        $T = $this->getTable();
        $limit = $Request->pluck('limit');
        $page = $Request->pluck('page') ?: 1;

        if($limit < 1 || $limit > $this->mLimitMax)
            $limit = $this->mLimit;

        /** @var PDOModelSelect $Search */

        if($T::EXPORT_AS_OBJECT) {
            $Search = $T->search();
        } else {
            $export = PDOColumn::FLAG_EXPORT;
            $select = array_keys($T->findColumns($export));
            $Search = $T->select($select);
        }

        $Descriptor = new API_GetBrowseDescriptor($T, $Search, $this);
        $Search
            ->setDescriptor($Descriptor)
            ->limit($limit)
            ->page($page);

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIGetBrowseCallbacks)
                $Handler->prepareQuery($Search, $Request);

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IReadAccess)
                $Handler->assertQueryReadAccess($Search, $T, $Request, IReadAccess::INTENT_SEARCH);

        return new PDOSearchResponse($Search);
    }


    /**
     * Render request as html and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr=null) {

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IPDOModelSearchRender)
            {
                try {
                    $Response = $this->execute($Request);
                    $Handler->renderSearch($Request, $Response);
                    return;
                } catch (\Exception $ex) {
                    $Handler->renderException($ex, $Request);
                    return;
                }
            }

        $Util = new APIRenderUtil($this);
        $Util->renderHTML($Request, $Attr);
    }
}


class API_GetBrowseDescriptor implements ISelectDescriptor {
    private $mTable, $mAPI, $mQuery, $mStatsCache;

    function __construct(PDOTable $Table, PDOSelect $Query, IAPI $API) {
        $this->mTable = $Table;
        $this->mQuery = $Query;
        $this->mAPI = $API;
    }

    public function getLimitedStats() {
        return $this->mQuery->getLimitedStats();
    }

    public function execFullStats($allowCache=true) {
        $Stats = $this->getLimitedStats();
        if(!$allowCache)
            $this->mStatsCache = NULL;
        return $this->mStatsCache ?: $this->mStatsCache = new PDOSelectStats(
            (int)$this->mQuery->execStats('count(*)')->fetchColumn(0),
            $Stats->getLimit(),
            $Stats->getOffset()
        );
    }

    /**
     * Return the column for a query row value
     * @param String $columnName the name of the column to be translated
     * @return \CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn
     */
    function getColumn($columnName) {
        return $this->mTable->getColumn($columnName);
    }

    /**
     * Return the API used for this query
     * @return IAPI
     */
    function getAPI() {
        return $this->mAPI;
    }
}