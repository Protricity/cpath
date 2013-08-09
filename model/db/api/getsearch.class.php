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
     * Construct an instance of this API
     * @param PDOModel|IReadAccess $Model the user source object for this API
     */
    function __construct(PDOModel $Model) {
        parent::__construct($Model);

        $this->mSearchColumns = $Model->findColumns($Model::Search ?: PDOColumn::FlagSearch);

        $this->addField('search', new APIRequiredParam("Search for ".$Model::getModelName()));
        $this->addField('search_by', new APIParam("Search by column. Allowed: [".implode(', ', array_keys($this->mSearchColumns))."]"));
        $this->addField('limit', new APIField("The Number of rows to return. Max=".$Model::SearchLimitMax));
        $this->addField('logic', new APIField("The search logic to use [AND, OR]. Default=OR"));
    }

//    /**
//     * Searches for Models based on specified columns and values.
//     * @param IRequest $Request the request that was used
//     * @param String $search the column value to search for
//     * @param mixed|NULL $search_by a string list (comma delimited) or array of columns to search for.
//     * Default is static::Search or columns with PDOColumn::FlagSearch set
//     * @param int $limit the number of rows to return. Default is 1
//     * @param string $logic 'OR' or 'AND' logic between columns. Default is 'OR'
//     * @param string|NULL $compare set WHERE logic for each column [=, >, LIKE, etc]. Default is '='
//     * @return PDOModelSelect - the select query.
//     * @throws \Exception if no columns were found
//     */
//    function buildQuery(IRequest $Request, $search, $search_by, $limit, $logic, $compare) {
//        if($search[0] == ':')
//            throw new \Exception("Tokens not allowed: " . $search);
//        $Model = $this->mModel;
//        return $Model::searchByColumns($search, $search_by, $limit, $logic, $compare);
//    }

    /**
     * Get the API Description
     * @return String description for this API
     */
    function getDescription() {
        return "Search for a " . $this->getModel()->getModelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     * @throws ModelNotFoundException if the Model was not found
     * @throws \Exception if no valid columns were found
     */
    function execute(IRequest $Request) {

        $Model = $this->getModel();
        $this->processRequest($Request);
        $limit = $Request->pluck('limit');
        $search = $Request->pluck('search');
        $search_by = $Request->pluck('search_by');
        $logic = $Request->pluck('logic') ?: 'OR';

        if($limit < 1 || $limit > $Model::SearchLimitMax)
            $limit = $Model::SearchLimit;

        if($Model::SearchWildCard) {
            if(strpos($search, '*') !== false)
                $search = str_replace('*', '%', $search);
            else
                $search .= '%';
        }

        if($search_by && !isset($this->mSearchColumns[$search_by]))
            throw new \Exception("Invalid search_by column: " . implode(', ', $this->mSearchColumns));

        $Model = $this->getModel();
        /** @var PDOModelSelect $Search */
        $Search = $Model::searchByColumns($search, $search_by, $limit, $logic, $Model::SearchWildCard ? 'LIKE' : '');

        if($Model instanceof IReadAccess)
            $Model->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_SEARCH);

        $results = $Search->fetchAll();
        if($Model instanceof IReadAccess)
            foreach($results as $result)
                /** @var IReadAccess $result */
                $result->assertQueryReadAccess($Search, $Request, IReadAccess::INTENT_SEARCH);

        return new Response("Found (".sizeof($results).") ".$Model::getModelName()."(s)", true, $results);
    }
}
