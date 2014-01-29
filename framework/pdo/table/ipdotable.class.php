<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table;

use CPath\Framework\PDO\Columns\PDOColumn;
use CPath\Framework\PDO\DB\PDODatabase;
use CPath\Interfaces\IBuildable;
use CPath\Response\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\IResponseAggregate;

interface IPDOTable extends IBuildable
{
    /**
     * @return \CPath\Framework\PDO\DB\PDODatabase
     */
    static function getDB();


    /**
     * Return all columns for this table
     * @return PDOColumn[]
     */
    static function loadAllColumns();

}

class ModelNotFoundException extends \Exception {}
class ColumnNotFoundException extends \Exception {}
class InvalidPermissionException extends \Exception {}
class ModelAlreadyExistsException extends \Exception implements IResponseAggregate {

    /**
     * @return IResponse
     */
    function createResponse() {
        $Response = new ExceptionResponse($this);
        $Response->setStatusCode(IResponse::STATUS_CONFLICT);
        return $Response;
    }
}


