<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;

use CPath\Handlers\Api\Interfaces\InvalidAPIException;
use CPath\Describable\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Response\IResponse;
use CPath\Model\DB\Interfaces\IAPIPostCallbacks;
use CPath\Model\DB\Interfaces\IAssignAccess;
use CPath\Model\DB\Interfaces\IWriteAccess;
use CPath\Response\Response;

class API_Put extends API_Base {

    private $mColumns;

    /**
     * Construct an instance of the GET API
     * @param PDOModel $Model the user source object for this API
     * @param String $_searchColumns varargs specifying a list of search fields to determine if a row exists
     * @throws \InvalidArgumentException if no search columns were provided
     */
    function __construct(PDOModel $Model, $_searchColumns) {
        parent::__construct($Model);
        $this->mColumns = is_array($_searchColumns) ? $_searchColumns : array_slice(func_get_args(), 1);
        if(!$this->mColumns || !$this->mColumns[0])
            throw new \InvalidArgumentException("Invalid Search Columns");
    }

    /**
     * Get the Object Description
     * @return \CPath\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Create or update a ".$this->getModel()->modelName();
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    final protected function setupFields() {
        $Model = $this->getModel();

        $fields = array();
        foreach($Model::findColumns($Model::INSERT ?: PDOColumn::FLAG_INSERT) as $Column)
            $fields[$Column->getName()] = $Column->generateAPIField();

        foreach($Model::findColumns($Model::UPDATE ?: PDOColumn::FLAG_UPDATE) as $Column)
            if(!isset($fields[$Column->getName()]))
                $fields[$Column->getName()] = $Column->generateAPIField(false);

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $fields = $Handler->preparePostFields($fields) ?: $fields;

        $this->addFields($fields);
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return \CPath\Response\IResponse|mixed the api call response with data, message, and status
     * @throws ModelNotFoundException if a duplicate row couldn't be found.
     * Warning: If this happens, there is an issue with this PDOModel's or this API's configuration
     * @throws InvalidAPIException if multiple duplicate rows were found
     */
    final protected function doExecute(IRequest $Request) {
        $Model = $this->getModel();

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAssignAccess)
                $Handler->assignAccessID($Request, IAssignAccess::INTENT_POST);

        $row = $Request->getDataPath();
        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $row = $Handler->preparePostInsert($row, $Request) ?: $row;

        try {
            if($Model instanceof PDOPrimaryKeyModel) {
                $NewModel = $Model::createAndLoad($row);
            } else {
                $NewModel = $Model::createAndFill($row);
            }

            $Response = new Response("Created " . $NewModel . " Successfully.", true, $NewModel);

            foreach($this->getHandlers() as $Handler)
                if($Handler instanceof IAPIPostCallbacks)
                    $Response = $Handler->onPostExecute($NewModel, $Request, $Response) ?: $Response;
        } catch (ModelAlreadyExistsException $ex) {

            $Query = $Model::search();
            foreach($this->mColumns as $columnName)
                $Query->where($columnName, $row[$columnName]);

            /** @var PDOPrimaryKeyModel $FoundModel */
            if(!$FoundModel = $Query->fetch())
                throw new ModelNotFoundException("ERROR: Duplicate row couldn't be found. Adjust your search fields: " . implode(', ', $this->mColumns));

            if($Query->fetch())
                throw new InvalidAPIException("ERROR: Multiple models found. Adjust your search fields: " . implode(', ', $this->mColumns));

            foreach($this->getHandlers() as $Handler)
                if($Handler instanceof IWriteAccess)
                    $Handler->assertWriteAccess($FoundModel, $Request, IWriteAccess::INTENT_PATCH);

            foreach($Model::findColumns($Model::UPDATE ?: PDOColumn::FLAG_UPDATE) as $Column)
                if(isset($row[$Column->getName()]))
                    $FoundModel->updateColumn($Column->getName(), $row[$Column->getName()], false);

            if($c = $FoundModel->commitColumns())
                $Response = new Response("Found and updated " . $FoundModel . " ({$c}) Successfully.", true, $FoundModel);
            else
                $Response = new Response("Found " . $FoundModel . " But no updates were made.", true, $FoundModel);
        }

        return $Response;
    }
}
