<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Interfaces\IRequest;
use CPath\Model\DB\Interfaces\ISecurityPolicy;

/**
 * Class Policy_Public implements a 'public' security policy that asserts no permissions
 * @package CPath\Model\DB
 */
class Policy_ReadOnly implements ISecurityPolicy {

    /**
     * Assert permission in default API calls such as GET, GET search, PATCH, and DELETE
     * Overwrite to enforce permission across API calls
     * @param PDOModel $Model the Model to assert access upon
     * @param IRequest $Request
     * @param int $intent the read intent. Typically IReadAccess::INTENT_GET or IReadAccess::INTENT_SEARCH
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     * @return void
     */
    function assertReadAccess(PDOModel $Model, IRequest $Request, $intent) { }

    /**
     * Assert read permissions by Limiting API search queries endpoints such as GET, GET search, PATCH, and DELETE
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @param int $intent the read intent. Typically IReadAccess::INTENT_SEARCH
     * @return void
     */
    function assertQueryReadAccess(PDOWhere $Select, IRequest $Request, $intent) { }

    /**
     * Assert permission in default API calls 'POST, PATCH, and DELETE'
     * @param PDOModel $Model the Model to assert access upon
     * Note: during POST, $Model has no values
     * @param IRequest $Request
     * @param int $intent the read intent.
     * Typically IWriteAccess::INTENT_POST, IWriteAccess::INTENT_PATCH or IWriteAccess::INTENT_DELETE.
     * Note: during IWriteAccess::INTENT_POST, the instance $Model contains no data.
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     */
    function assertWriteAccess(PDOModel $Model, IRequest $Request, $intent) {
        throw new InvalidPermissionException("No write access is granted in 'read only' mode");
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
        throw new InvalidPermissionException("No write access is granted in 'read only' mode");
    }
}
