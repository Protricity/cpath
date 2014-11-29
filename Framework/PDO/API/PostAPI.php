<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;

use CPath\Framework\PDO\Interfaces\IAPIPostCallbacks;
use CPath\Framework\PDO\Interfaces\IAssignAccess;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

class PostAPI extends AbstractPDOAPI {

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     */
    final protected function setupFields() {
        $T = $this->getTable();

        $fields = array();
        foreach($T->findColumns(PDOColumn::FLAG_INSERT) as $Column)
            $fields[$Column->getName()] = $Column->generateAPIField();

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $fields = $Handler->preparePostFields($fields) ?: $fields;

        $this->addFields($fields);
    }

    /**
     * Get the Object Description
     * @return \CPath\Data\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Create a new ".$this->getTable()->getModelName();
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param \CPath\Request\IRequest $Request the IRequest inst for this render which contains the request and args
     * @param Array $args additional arguments for this execution
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final function execute(IRequest $Request, $args) {
        $T = $this->getTable();

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAssignAccess)
                $Handler->assignAccessID($Request, IAssignAccess::INTENT_POST);

        $row = $Request->getDataPath();
        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $row = $Handler->preparePostInsert($row, $Request) ?: $row;

        if($T instanceof PDOPrimaryKeyTable) {
            $NewModel = $T->createAndLoad($row);
        } else {
            $NewModel = $T->createAndFill($row);
        }

        $Response = new DataResponse("Created " . $NewModel . " Successfully.", true, $NewModel);

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostCallbacks)
                $Response = $Handler->onPostExecute($NewModel, $Request, $Response) ?: $Response;

        return $Response;
    }
}
