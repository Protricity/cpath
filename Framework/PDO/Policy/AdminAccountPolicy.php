<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Policy;


use CPath\Framework\PDO\InvalidPermissionException;
use CPath\Framework\PDO\Query\PDOWhere;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Request\IRequest;
use CPath\Framework\User\Predicates\IsAdmin;
use CPath\Framework\User\Util\UserUtil;

/**
 * Class AdminAccountPolicy implements an Admin-Only security policy
 * @package CPath\Framework\PDO
 */
class AdminAccountPolicy extends UserAccountViewerPolicy {

    /** @var UserUtil  */
    private $mUserTable;

    /**
     * Create an 'admin only' security policy
     * @param \CPath\Framework\PDO\Templates\User\Model\PDOUserModel $User an instance of the current user session
     */
    public function __construct(PDOUserModel $User) {
        $this->mUserTable = new UserUtil($User);
    }

    /**
     * Assert permission in default API calls such as GET, GET search, PATCH, and DELETE
     * Overwrite to enforce permission across API calls
     * @param \CPath\Framework\PDO\Table\Model\Types\PDOModel $Model the User Model to assert access upon
     * @param IRequest $Request
     * @param int $intent the read intent. Typically IReadAccess::INTENT_GET or IReadAccess::INTENT_SEARCH
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     * @return void
     */
    function assertReadAccess(PDOModel $Model, IRequest $Request, $intent) {
        if(!$this->mUserTable->hasRole(new IsAdmin))
            throw new InvalidPermissionException("No permission to modify " . $Model);
    }

    /**
     * Assert read permissions by Limiting API search queries endpoints such as GET, GET search, PATCH, and DELETE
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @param int $intent the read intent. Typically IReadAccess::INTENT_SEARCH
     * @throws InvalidPermissionException
     * @internal param \CPath\Framework\PDO\Table\PDOTable $Table The table instance
     * @return void
     */
    function assertQueryReadAccess(PDOWhere $Select, IRequest $Request, $intent) {
        if(!$this->mUserTable->hasRole(new IsAdmin))
            throw new InvalidPermissionException("No permission to query");
    }

    /**
     * Assert permission in default API calls 'POST, PATCH, and DELETE'
     * @param \CPath\Framework\PDO\Table\Model\Types\PDOModel $User the User Model to assert access upon
     * Note: during POST, $Model has no values
     * @param IRequest $Request
     * @param int $intent the read intent.
     * Typically IWriteAccess::INTENT_POST, IWriteAccess::INTENT_PATCH or IWriteAccess::INTENT_DELETE.
     * Note: during IWriteAccess::INTENT_POST, the instance $Model contains no data.
     * @throws InvalidPermissionException if the user account is not an Admin account
     */
    function assertWriteAccess(PDOModel $User, IRequest $Request, $intent) {
        if(!$this->mUserTable->hasRole(new IsAdmin))
            throw new InvalidPermissionException("No permission to query");
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
        if(!$this->mUserTable->hasRole(new IsAdmin))
            throw new InvalidPermissionException("No permission to query");
    }

    /**
     * Return the user model instance
     * @return UserUtil
     */
    function getUserAccount() { return $this->mUserTable; }
}
