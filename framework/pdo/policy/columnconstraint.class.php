<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;


use CPath\Framework\PDO\Interfaces\ISecurityPolicy;
use CPath\Framework\PDO\Interfaces\IWriteAccess;
use CPath\Framework\PDO\Model\PDOModel;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Table\InvalidPermissionException;
use CPath\Framework\PDO\Table\PDOTable;
use CPath\Framework\Request\Interfaces\IRequest;

/**
 * Class Policy_Public implements a 'public' security policy that asserts no permissions
 * @package CPath\Framework\PDO
 */
class Policy_ColumnConstraint implements ISecurityPolicy {

    private
        $mTable,
        $mColumn,
        $mValue,
        $mAllowDelete;

    function __construct(PDOTable $Table, $columnName, $columnValue, $allowDelete=false) {
        $this->mTable = $Table;
        $this->mColumn = $columnName;
        $this->mValue = $columnValue;
        $this->mAllowDelete = $allowDelete;
    }

    /**
     * Assert permission in default API calls such as GET, GET search, PATCH, and DELETE
     * Overwrite to enforce permission across API calls
     * @param PDOModel $Model the Model to assert access upon
     * @param IRequest $Request
     * @param int $intent the read intent. Typically IReadAccess::INTENT_GET or IReadAccess::INTENT_SEARCH
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     * @return void
     */
    function assertReadAccess(PDOModel $Model, IRequest $Request, $intent) {
        if($Model->columnValue($this->mColumn) !== $this->mValue)
            throw new InvalidPermissionException("No permission to modify " . $Model);
    }

    /**
     * Assert read permissions by Limiting API search queries endpoints such as GET, GET search, PATCH, and DELETE
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @param int $intent the read intent. Typically IReadAccess::INTENT_SEARCH
     * @return void
     */
    function assertQueryReadAccess(PDOWhere $Select, IRequest $Request, $intent) {
        $Select->where($this->mColumn, $this->mValue);
    }

    /**
     * Assert permission in default API calls 'POST, PATCH, and DELETE'
     * @param PDOModel $Model the Model to assert access upon
     * @param IRequest $Request
     * @param int $intent the read intent.
     * Typically IWriteAccess::INTENT_POST, IWriteAccess::INTENT_PATCH or IWriteAccess::INTENT_DELETE.
     * Note: during IWriteAccess::INTENT_POST, the instance $Model contains no data.
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     */
    function assertWriteAccess(PDOModel $Model, IRequest $Request, $intent) {
        $value = $this->mValue;
        switch($intent) {
            case IWriteAccess::INTENT_PATCH:
                if($Model->columnValue($this->mColumn) != $value)
                    throw new InvalidPermissionException("No permission to modify " . $Model);
                break;
            case IWriteAccess::INTENT_DELETE:
                if(!$this->mAllowDelete
                    || $Model->columnValue($this->mColumn) != $value)
                    throw new InvalidPermissionException("No permission to delete " . $Model);
                break;
        }
    }

    /**
     * Assign Access ID and assert permission in default POST API calls.
     * Typically this involves updating the $Request column (ex. user_id, owner_id) with the correct access identifier before the POST occurs.
     * Additionally, an InvalidPermissionException should be thrown if there is no permission to POST
     * @param IRequest $Request
     * @param int $intent the read intent. Typically IAssignAccess::INTENT_POST
     * @throws InvalidPermissionException if the user does not have permission to create this Model
     */
    function assignAccessID(IRequest $Request, $intent) {
        $Request[$this->mColumn] = $this->mValue;
    }
}
