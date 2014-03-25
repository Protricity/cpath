<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;


use CPath\Describable\IDescribable;
use CPath\Framework\API\Field\Field;
use CPath\Framework\API\Field\Param;
use CPath\Framework\API\Field\RequiredParam;
use CPath\Framework\PDO\Interfaces\IAPIGetBrowseCallbacks;
use CPath\Framework\PDO\Interfaces\IPDOModelSearchRender;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Request\Interfaces\IRequest;

class GetSearchAPI extends GetBrowseAPI implements IAPIGetBrowseCallbacks {
    private $mColumns;

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupFields() {
        $T = $this->getTable();

        $this->mColumns = $T->findColumns(PDOColumn::FLAG_SEARCH);

        $this->addField(new RequiredParam('search', "SEARCH for ".$T->getModelName()));
        $this->addField(new Param('search_by', "SEARCH by column. Allowed: [".implode(', ', array_keys($this->mColumns))."]"));
        //$this->addField(new Field('limit', "The Number of rows to return. Max=".$Model::SEARCH_LIMIT_MAX));
        $this->addField(new Field('logic', "The search logic to use [AND, OR]. Default=OR"));

        parent::setupFields();
    }


    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Search for a " . $this->getTable()->getModelName();
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
                    $Search = $this
                        ->execute($Request)
                        ->getQuery();
                    $Handler->renderSearch($Request, $Search);
                    return;
                } catch (\Exception $ex) {
                    $Handler->renderException($ex, $Request);
                    return;
                }
            }

        parent::renderHTML($Request, $Attr);
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

        $T = $this->getTable();
        $search = $Request->pluck('search');
        $search_by = $Request->pluck('search_by');
        $logic = $Request->pluck('logic') ?: 'OR';


        if(strcasecmp($logic, 'OR')===0)
            $Select->setFlag(PDOWhere::LOGIC_OR);

        if($search_by && !isset($this->mColumns[$search_by]))
            throw new \Exception("Invalid search_by column: " . implode(', ', $this->mColumns));

        $columns = $T->findColumns($search_by ?: PDOColumn::FLAG_SEARCH);

        if(!is_int($search)) {
            $columns2 = array();
            foreach($columns as $Column)
                if(!$Column->hasFlag(PDOColumn::FLAG_NUMERIC))
                    $columns2[$Column->getName()] = $Column;
            if($columns2)
                $columns = $columns2;
        }

        if(!$columns)
            throw new \Exception("No SEARCH fields defined in ".$T->getModelName());

        if($T::SEARCH_WILDCARD) {
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