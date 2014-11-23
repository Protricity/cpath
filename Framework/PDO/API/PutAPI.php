<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;

use CPath\Framework\API\Exceptions\APIException;
use CPath\Framework\PDO\Interfaces\IAPIPostCallbacks;
use CPath\Framework\PDO\Interfaces\IAssignAccess;
use CPath\Framework\PDO\Interfaces\IWriteAccess;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelAlreadyExistsException;
use CPath\Framework\PDO\Table\Model\Exceptions\ModelNotFoundException;
use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

class PutAPI extends AbstractPDOAPI {

    private $mColumns;

    /**
     * Construct an inst of the GET API
     * @param PDOTable $Table the table for this API
     * @param String $_searchColumns varargs specifying a list of search fields to determine if a row exists
     * @throws \InvalidArgumentException if no search columns were provided
     */
    function __construct(PDOTable $Table, $_searchColumns) {
        parent::__construct($Table);
        $this->mColumns = is_array($_searchColumns) ? $_searchColumns : array_slice(func_get_args(), 1);
        if(!$this->mColumns || !$this->mColumns[0])
            throw new \InvalidArgumentException("Invalid Search Columns");
    }

    /**
     * Get the Object Description
     * @return \CPath\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Create or update a ".$this->getTable()->getModelName();
    }

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    final protected function setupFields() {
        $fields = array();
        foreach($this->getTable()->findColumns(PDOColumn::FLAG_INSERT) as $Column)
            $fields[$Column->getName()] = $Column->generateAPIField();

        foreach($this->getTable()->findColumns(PDOColumn::FLAG_UPDATE) as $Column)
            if(!isset($fields[$Column->getName()]))
                $fields[$Column->getName()] = $Column->generateAPIField(false);

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $fields = $Handler->preparePostFields($fields) ?: $fields;

        $this->addFields($fields);
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and args
     * @param Array $args additional arguments for this execution
     * @return IResponse|mixed the api call response with data, message, and status
     * @throws ModelNotFoundException if a duplicate row couldn't be found.
     * Warning: If this happens, there is an issue with this PDOModel's or this API's configuration
     * @throws \CPath\Framework\API\Exceptions\APIException if multiple duplicate rows were found
     */
    final function execute(IRequest $Request, $args) {
        $Table = $this->getTable();

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAssignAccess)
                $Handler->assignAccessID($Request, IAssignAccess::INTENT_POST);

        $row = $Request->getDataPath();
        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $row = $Handler->preparePostInsert($row, $Request) ?: $row;

        try {
            if($Table instanceof PDOPrimaryKeyTable) {
                $NewModel = $Table->createAndLoad($row);
            } else {
                $NewModel = $Table->createAndFill($row);
            }

            $Response = new DataResponse("Created " . $NewModel . " Successfully.", true, $NewModel);

            foreach($this->getHandlers() as $Handler)
                if($Handler instanceof IAPIPostCallbacks)
                    $Response = $Handler->onPostExecute($NewModel, $Request, $Response) ?: $Response;
        } catch (ModelAlreadyExistsException $ex) {

            $Query = $Table->search();
            foreach($this->mColumns as $columnName)
                $Query->where($columnName, $row[$columnName]);

            /** @var PDOPrimaryKeyModel $FoundModel */
            if(!$FoundModel = $Query->fetch())
                throw new ModelNotFoundException($Table, $this->mColumns);
                //throw new ModelNotFoundException("ERROR: Duplicate row couldn't be found. Adjust your search fields: " . implode(', ', $this->mColumns));

            if($Query->fetch())
                throw new APIException("ERROR: Multiple models found. Adjust your search fields: " . implode(', ', $this->mColumns));

            foreach($this->getHandlers() as $Handler)
                if($Handler instanceof IWriteAccess)
                    $Handler->assertWriteAccess($FoundModel, $Request, IWriteAccess::INTENT_PATCH);

            foreach($Table->findColumns(PDOColumn::FLAG_UPDATE) as $Column)
                if(isset($row[$Column->getName()]))
                    $FoundModel->updateColumn($Column->getName(), $row[$Column->getName()], false);

            if($c = $FoundModel->commitColumns())
                $Response = new DataResponse("Found and updated " . $FoundModel . " ({$c}) Successfully.", true, $FoundModel);
            else
                $Response = new DataResponse("Found " . $FoundModel . " But no updates were made.", true, $FoundModel);
        }

        return $Response;
    }
}
