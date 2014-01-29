<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;


use CPath\Framework\PDO\Model\PDOModel;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Table\InvalidPermissionException;
use CPath\Handlers\API;
use CPath\Interfaces\IRequest;

/**
 * Class Policy_Public implements a 'public' security policy that asserts no permissions
 * @package CPath\Framework\PDO
 */
class Policy_CallbackConstraint extends Policy_ColumnConstraint {

    /**
     * @param PDOModel $Model
     * @param $columnName
     * @param Callable $callback determines the constraint value
     * new function(PDOModel $Model) { ...; return $value; }
     * @param bool $allowDelete
     * @throws \InvalidArgumentException if $callback is not callable
     */
    function __construct(PDOModel $Model, $columnName, $callback, $allowDelete=false) {
        if(!is_callable($callback))
            throw new \InvalidArgumentException("Invalid callback provided. 'columnValue' is not callable");
        parent::__construct($Model, $columnName, $callback, $allowDelete);
    }

    protected function getValue(PDOModel $Model=NULL) {
        return call_user_func(parent::getValue($Model), $Model);
    }

    /**
     * Assert read permissions by Limiting API search queries endpoints such as GET, GET search, PATCH, and DELETE
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @param int $intent the read intent. Typically IReadAccess::INTENT_SEARCH
     * @return void
     * @throws InvalidPermissionException
     */
    function assertQueryReadAccess(PDOWhere $Select, IRequest $Request, $intent) {
        throw new InvalidPermissionException("Security policy 'CallbackConstraint' does not support queries" );
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
        throw new InvalidPermissionException("Security policy 'CallbackConstraint' does not support POST" );
    }

}
