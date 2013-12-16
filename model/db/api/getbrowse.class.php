<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\Api\Field;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\API;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\Interfaces\IAPIGetBrowseCallbacks;
use CPath\Model\DB\Interfaces\IPDOModelSearchRender;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\DB\Interfaces\ISelectDescriptor;

class API_GetBrowse extends API_Base {

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupFields() {

        $Model = $this->getModel();
//        $this->addField('search', new RequiredParam("SEARCH for ".$Model::modelName()));
//        $this->addField('search_by', new Param("SEARCH by column. Allowed: [".implode(', ', array_keys($this->mColumns))."]"));
//        $this->addField('logic', new Field("The search logic to use [AND, OR]. Default=OR"));
        $this->addField('limit', new Field("The number of rows to return. Max=".$Model::SEARCH_LIMIT_MAX));
        $this->addField('page', new Field("The page number to return"));
    }


    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Browse for a " . $this->getModel()->modelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     * @throws ModelNotFoundException if the Model was not found
     * @throws \Exception if no valid columns were found
     */
    final protected function doExecute(IRequest $Request) {

        $Model = $this->getModel();
        $limit = $Request->pluck('limit');
        $page = $Request->pluck('page') ?: 1;

        if($limit < 1 || $limit > $Model::SEARCH_LIMIT_MAX)
            $limit = $Model::SEARCH_LIMIT;

        $Model = $this->getModel();
        /** @var PDOModelSelect $Search */

        if($Model::EXPORT_OBJECT) {
            $Search = $Model::search();
        } else {
            $export = $Model::EXPORT ?: PDOColumn::FLAG_EXPORT;
            $select = array_keys($Model::findColumns($export));
            $Search = $Model::select($select);
        }

        $Descriptor = new API_GetBrowseDescriptor($Model, $Search, $this);
        $Search
            ->setDescriptor($Descriptor)
            ->limit($limit)
            ->page($page);

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIGetBrowseCallbacks)
                $Handler->prepareQuery($Search, $Request);

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IReadAccess)
                $Handler->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_SEARCH);

        $Stats = $Descriptor->execFullStats();
        $c = $Stats->getTotal();
        return new SearchResponse("Browsing ({$c}) " . $Model::modelName() . " results", $Search);
    }


    /**
     * Sends headers, executes the request, and renders an IResponse as HTML
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @return void
     */
    public function renderHTML(IRequest $Request) {

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IPDOModelSearchRender)
            {
                try {
                    /** @var SearchResponse $Response */
                    $Response = $this->executeOrThrow($Request);
                    $Handler->renderSearch($Request, $Response);
                    return;
                } catch (\Exception $ex) {
                    $Handler->renderException($ex, $Request);
                    return;
                }
            }

        parent::renderHTML($Request);
    }
}


class API_GetBrowseDescriptor implements ISelectDescriptor {
    private $mModel, $mAPI, $mQuery, $mStatsCache;

    function __construct(PDOModel $Model, PDOSelect $Query, IAPI $API) {
        $this->mModel = $Model;
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
     * @return PDOColumn
     */
    function getColumn($columnName) {
        $Model = $this->mModel;
        return $Model::loadColumn($columnName);
    }

    /**
     * Return the API used for this query
     * @return IAPI
     */
    function getAPI() {
        return $this->mAPI;
    }
}