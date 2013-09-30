<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIParam;
use CPath\Handlers\APIRequiredParam;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\Response;

class API_GetSearch extends API_Base {
    private $mSearchColumns;

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupAPI() {
        $Model = $this->getModel();

        $this->mSearchColumns = $Model->findColumns($Model::SEARCH ?: PDOColumn::FLAG_SEARCH);

        $this->addField('search', new APIRequiredParam("SEARCH for ".$Model::modelName()));
        $this->addField('search_by', new APIParam("SEARCH by column. Allowed: [".implode(', ', array_keys($this->mSearchColumns))."]"));
        $this->addField('limit', new APIField("The Number of rows to return. Max=".$Model::SEARCH_LIMIT_MAX));
        $this->addField('logic', new APIField("The search logic to use [AND, OR]. Default=OR"));
    }

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Search for a " . $this->getModel()->modelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     * @throws ModelNotFoundException if the Model was not found
     * @throws \Exception if no valid columns were found
     */
    final protected function doExecute(IRequest $Request) {

        $Model = $this->getModel();
        $this->processRequest($Request);
        $limit = $Request->pluck('limit');
        $search = $Request->pluck('search');
        $search_by = $Request->pluck('search_by');
        $logic = $Request->pluck('logic') ?: 'OR';

        if($limit < 1 || $limit > $Model::SEARCH_LIMIT_MAX)
            $limit = $Model::SEARCH_LIMIT;

        if($Model::SEARCH_WILDCARD) {
            if(strpos($search, '*') !== false)
                $search = str_replace('*', '%', $search);
            else
                $search .= '%';
        }

        if($search_by && !isset($this->mSearchColumns[$search_by]))
            throw new \Exception("Invalid search_by column: " . implode(', ', $this->mSearchColumns));

        $Model = $this->getModel();
        /** @var PDOModelSelect $Search */

        $export = $Model::EXPORT_SEARCH ?: $Model::EXPORT ?: PDOColumn::FLAG_EXPORT;
        $Search = $Model::selectByColumns($export, $search, $search_by, $limit, $logic, $Model::SEARCH_WILDCARD ? 'LIKE' : '');

        $Policy = $this->getSecurityPolicy();

        $Policy->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_SEARCH);
        if($Model instanceof IReadAccess)
            $Model->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_SEARCH);

        $results = $Search->fetchAll();

        foreach($results as $ResultModel)
            $Policy->assertReadAccess($ResultModel, $Request, IReadAccess::INTENT_SEARCH);
        if($Model instanceof IReadAccess)
            foreach($results as $ResultModel)
                $Model->assertReadAccess($ResultModel, $Request, IReadAccess::INTENT_SEARCH);

        return new Response("Found (".sizeof($results).") ".$Model::modelName()."(s)", true, $results);
    }
}
