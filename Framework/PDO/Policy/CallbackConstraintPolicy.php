<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Policy;


use CPath\Framework\PDO\Interfaces\ISecurityPolicy;
use CPath\Framework\PDO\Interfaces\IWriteAccess;
use CPath\Framework\PDO\InvalidPermissionException;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Request\IRequest;

/**
 * Class PublicPolicy implements a 'public' security policy that asserts no permissions
 * @package CPath\Framework\PDO
 */
class CallbackConstraintPolicy implements ISecurityPolicy {

    private
        $mTable,
        $mColumn,
        $mCallback,
        $mAllowDelete;

    function __construct(PDOTable $Table, $columnName, $callback, $allowDelete=false) {
        if(!is_callable($callback))
            throw new \InvalidArgumentException("Invalid callback provided. 'columnValue' is not callable");

        $this->mTable = $Table;
        $this->mColumn = $columnName;
        $this->mCallback = $callback;
        $this->mAllowDelete = $allowDelete;
    }

    protected function getValue() {
        return call_user_func($this->mCallback);
    }

    /**
     * Assert permission in default API calls such as GET, GET search, PATCH, and DELETE
     * Overwrite to enforce permission across API calls
     * @param \CPath\Framework\PDO\Table\Model\Types\PDOModel $Model the Model to assert access upon
     * @param \CPath\Request\IRequest $Request
     * @param int $intent the read intent. Typically IReadAccess::INTENT_GET or IReadAccess::INTENT_SEARCH
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     * @return void
     */
    function assertReadAccess(PDOModel $Model, IRequest $Request, $intent) {
        if($Model->columnValue($this->mColumn) !== $this->getValue())
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
        $Select->where($this->mColumn, $this->getValue());
    }

    /**
     * Assert permission in default API calls 'POST, PATCH, and DELETE'
     * @param PDOModel $Model the Model to assert access upon
     * @param \CPath\Request\IRequest $Request
     * @param int $intent the read intent.
     * Typically IWriteAccess::INTENT_POST, IWriteAccess::INTENT_PATCH or IWriteAccess::INTENT_DELETE.
     * Note: during IWriteAccess::INTENT_POST, the inst $Model contains no data.
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     */
    function assertWriteAccess(PDOModel $Model, IRequest $Request, $intent) {
        switch($intent) {
            case IWriteAccess::INTENT_PATCH:
                if($Model->columnValue($this->mColumn) != $this->getValue())
                    throw new InvalidPermissionException("No permission to modify " . $Model);
                break;
            case IWriteAccess::INTENT_DELETE:
                if(!$this->mAllowDelete
                    || $Model->columnValue($this->mColumn) != $this->getValue())
                    throw new InvalidPermissionException("No permission to delete " . $Model);
                break;
        }
    }

    /**
     * Assign Access ID and assert permission in default POST API calls.
     * Typically this involves updating the $Request column (ex. user_id, owner_id) with the correct access identifier before the POST occurs.
     * Additionally, an InvalidPermissionException should be thrown if there is no permission to POST
     * @param \CPath\Request\IRequest $Request
     * @param int $intent the read intent. Typically IAssignAccess::INTENT_POST
     * @throws InvalidPermissionException if the user does not have permission to create this Model
     */
    function assignAccessID(IRequest $Request, $intent) {
        $Request[$this->mColumn] = $this->getValue();
    }

}
