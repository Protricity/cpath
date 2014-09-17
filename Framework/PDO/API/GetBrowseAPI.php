<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;

use CPath\Framework\API\Field\Field;
use CPath\Framework\API\Render\Util\APIRenderUtil;
use CPath\Framework\API\Util\APIExecuteUtil;
use CPath\Framework\PDO\Misc\API_GetBrowseDescriptor;
use CPath\Framework\PDO\Interfaces\IAPIGetBrowseCallbacks;
use CPath\Framework\PDO\Interfaces\IPDOModelSearchRender;
use CPath\Framework\PDO\Interfaces\IReadAccess;
use CPath\Framework\PDO\Response\PDOSearchResponse;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException;
use CPath\Framework\PDO\Table\Model\Query\PDOModelSelect;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class GetBrowseAPI extends AbstractPDOAPI implements IRenderHTML
{

    private $mLimit, $mLimitMax;

    /**
     * Construct an instance of the GET API
     * @param PDOTable|IReadAccess $Table the PDOTable for this API
     * @param int $limit
     * @param int $limitMax
     * PRIMARY key is already included
     */
    function __construct(PDOTable $Table, $limit = 25, $limitMax = 100)
    {
        $this->mTable = $Table;
        $this->mLimit = $limit;
        $this->mLimitMax = $limitMax;
    }

    /**
     * @return PDOPrimaryKeyTable
     */
    function getTable() {
        return $this->mTable;
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    protected function setupFields()
    {
//        $this->addField('search', new RequiredParam("SEARCH for ".$Model::modelName()));
//        $this->addField('search_by', new Param("SEARCH by column. Allowed: [".implode(', ', array_keys($this->mColumns))."]"));
//        $this->addField('logic', new Field("The search logic to use [AND, OR]. Default=OR"));
        $this->addField(new Field('limit', "The number of rows to return. Max=" . $this->mLimitMax));
        $this->addField(new Field('page', "The page number to return"));
    }


    /**
     * Get the Object Description
     * @return \CPath\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable()
    {
        return "Browse for a " . $this->getTable()->getModelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and args
     * @param Array $args additional arguments for this execution
     * @return PDOSearchResponse the api call response with data, message, and status
     * @throws ModelNotFoundException if the Model was not found
     * @throws \Exception if no valid columns were found
     */
    final function execute(IRequest $Request, $args)
    {

        $Util = new APIExecuteUtil($this);
        $Util->processRequest($Request);

        $T = $this->getTable();
        $limit = $Request->pluck('limit');
        $page = $Request->pluck('page') ? : 1;

        if ($limit < 1 || $limit > $this->mLimitMax)
            $limit = $this->mLimit;

        /** @var PDOModelSelect $Search */

        if ($T::EXPORT_AS_OBJECT) {
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

        foreach ($this->getHandlers() as $Handler)
            if ($Handler instanceof IAPIGetBrowseCallbacks)
                $Handler->prepareQuery($Search, $Request);

        foreach ($this->getHandlers() as $Handler)
            if ($Handler instanceof IReadAccess)
                $Handler->assertQueryReadAccess($Search, $T, $Request, IReadAccess::INTENT_SEARCH);

        return new PDOSearchResponse($Search);
    }


    /**
     * Render request as html and sends headers as necessary
     * @param \CPath\Framework\PDO\API\IRenderRequest|\CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr
     * @internal param $ \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTML(IRenderRequest $Request, IAttributes $Attr = null)
    {

        foreach ($this->getHandlers() as $Handler)
            if ($Handler instanceof IPDOModelSearchRender) {
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