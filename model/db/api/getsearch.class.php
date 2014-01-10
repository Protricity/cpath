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
use CPath\Handlers\Api\Param;
use CPath\Handlers\API;
use CPath\Handlers\Api\RequiredParam;
use CPath\Describable\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Response\IResponse;
use CPath\Model\DB\Interfaces\IAPIGetBrowseCallbacks;
use CPath\Model\DB\Interfaces\IPDOModelSearchRender;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\DB\Interfaces\ISelectDescriptor;
use CPath\Response\Response;

class API_GetSearch extends API_GetBrowse implements IAPIGetBrowseCallbacks {
    private $mColumns;

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupFields() {
        $Model = $this->getModel();

        $this->mColumns = $Model->findColumns($Model::SEARCH ?: PDOColumn::FLAG_SEARCH);

        $this->addField('search', new RequiredParam("SEARCH for ".$Model::modelName()));
        $this->addField('search_by', new Param("SEARCH by column. Allowed: [".implode(', ', array_keys($this->mColumns))."]"));
        //$this->addField('limit', new Field("The Number of rows to return. Max=".$Model::SEARCH_LIMIT_MAX));
        $this->addField('logic', new Field("The search logic to use [AND, OR]. Default=OR"));

        parent::setupFields();
    }


    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Search for a " . $this->getModel()->modelName();
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
                    $Model = $this->executeOrThrow($Request)->getDataPath();
                    $Handler->renderSearch($Model, $Request);
                    return;
                } catch (\Exception $ex) {
                    $Handler->renderException($ex, $Request);
                    return;
                }
            }

        parent::renderHTML($Request);
    }

    /**
     * Prepare a Search query
     * Use this interface call to constrain a query by adding WHERE statements
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @return void
     * @throws \Exception
     */
    function prepareQuery(PDOWhere $Select, IRequest $Request) {

        $Model = $this->getModel();
        $search = $Request->pluck('search');
        $search_by = $Request->pluck('search_by');
        $logic = $Request->pluck('logic') ?: 'OR';


        if(strcasecmp($logic, 'OR')===0)
            $Select->setFlag(PDOWhere::LOGIC_OR);

        if($search_by && !isset($this->mColumns[$search_by]))
            throw new \Exception("Invalid search_by column: " . implode(', ', $this->mColumns));

        $columns = $Model::findColumns($search_by ?: $Model::SEARCH ?: PDOColumn::FLAG_SEARCH);

        if(!is_int($search)) {
            $columns2 = array();
            foreach($columns as $name => $Column)
                if(!$Column->isFlag(PDOColumn::FLAG_NUMERIC))
                    $columns2[$name] = $Column;
            if($columns2)
                $columns = $columns2;
        }

        if(!$columns)
            throw new \Exception("No SEARCH fields defined in ".$Model::modelName());

        if($Model::SEARCH_WILDCARD) {
            if(strpos($search, '*') !== false)
                $search = str_replace('*', '%', $search);
            else
                $search .= '%';
            foreach($columns as $name=>$Column)
                $Select->where($name . ' LIKE', $search);
        } else {
            foreach($columns as $name=>$Column)
                $Select->where($name, $search);
        }
    }
}