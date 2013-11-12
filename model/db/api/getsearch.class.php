<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\Api\Field;
use CPath\Handlers\Api\Param;
use CPath\Handlers\Api\RequiredParam;
use CPath\Interfaces\IDescribable;
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
    protected function setupFields() {
        $Model = $this->getModel();

        $this->mSearchColumns = $Model->findColumns($Model::SEARCH ?: PDOColumn::FLAG_SEARCH);

        $this->addField('search', new RequiredParam("SEARCH for ".$Model::modelName()));
        $this->addField('search_by', new Param("SEARCH by column. Allowed: [".implode(', ', array_keys($this->mSearchColumns))."]"));
        $this->addField('limit', new Field("The Number of rows to return. Max=".$Model::SEARCH_LIMIT_MAX));
        $this->addField('logic', new Field("The search logic to use [AND, OR]. Default=OR"));
    }


    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Search for a " . $this->getModel()->modelName();
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
        $select = array_keys($Model::findColumns($export));
        $Search = $Model::selectByColumns($select, $search, $search_by, $limit, $logic, $Model::SEARCH_WILDCARD ? 'LIKE' : '');

        $Policy = $this->getSecurityPolicy();

        $Policy->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_SEARCH);
        if($Model instanceof IReadAccess)
            $Model->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_SEARCH);

        $results = $Search->fetchAll();

        foreach($results as $ResultModel)
            if($ResultModel instanceof IReadAccess)
                $Policy->assertReadAccess($ResultModel, $Request, IReadAccess::INTENT_SEARCH);

        if($Model instanceof IReadAccess)
            foreach($results as $ResultModel)
                $Model->assertReadAccess($ResultModel, $Request, IReadAccess::INTENT_SEARCH);

        return new Response("Found (".sizeof($results).") ".$Model::modelName()."(s)", true, $results);
    }
}
