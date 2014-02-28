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
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Framework\PDO\Templates;
use CPath\Framework\Request\Interfaces\IRequest;

/**
 * Class PublicPolicy implements a 'public' security policy that asserts no permissions
 * @package CPath\Framework\PDO
 */
class UserAccountViewerPolicy implements ISecurityPolicy {
    private $mTable;
    private $mReadOther;
    private $mDelete;

    /**
     * Create a User Account security policy. This should only be used on PDOUserModels
     * @param Templates\User\Table\PDOUserTable $Table
     * @param bool $readOtherUsers allow 'GET' and 'GET search' on other user accounts
     * @param bool $deleteOwn allow 'DELETE' on own user account
     * @internal param \CPath\Framework\PDO\Templates\User\Model\PDOUserModel $User an empty instance of the UserModel
     */
    public function __construct(PDOUserTable $Table, $readOtherUsers=false, $deleteOwn=false) {
        $this->mTable = $Table;
        $this->mReadOther = $readOtherUsers;
        $this->mDelete = $deleteOwn;
    }

    /**
     * Assert permission in default API calls such as GET, GET search, PATCH, and DELETE
     * Overwrite to enforce permission across API calls
     * @param PDOUserModel|PDOModel $User the User Model to assert access upon
     * @param IRequest $Request
     * @param int $intent the read intent. Typically IReadAccess::INTENT_GET or IReadAccess::INTENT_SEARCH
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     * @return void
     */
    function assertReadAccess(PDOModel $User, IRequest $Request, $intent) {
        if(!$this->mReadOther) {
            $T = $this->mTable;
            $id = $T->loadBySession()->getID(); // Assert logged in
            if($User->columnValue($T::COLUMN_ID) !== $id)
                throw new InvalidPermissionException("No permission to modify " . $User);
        }
    }

    /**
     * Assert read permissions by Limiting API search queries endpoints such as GET, GET search, PATCH, and DELETE
     * @param PDOWhere $Select the query statement to limit.
     * @param IRequest $Request The api request to process and or validate validate
     * @param int $intent the read intent. Typically IReadAccess::INTENT_SEARCH
     * @return void
     */
    function assertQueryReadAccess(PDOWhere $Select, IRequest $Request, $intent) {
        if(!$this->mReadOther) {
            $T = $this->mTable;
            $id = $T->loadBySession()->getID();    // Assert logged in
            $Select->where($T::COLUMN_ID, $id);  // TODO: kinda silly no?
        }
    }

    /**
     * Assert permission in default API calls 'POST, PATCH, and DELETE'
     * @param \CPath\Framework\PDO\Templates\User\Model\PDOUserModel|PDOModel $User the User Model to assert access upon
     * Note: during POST, $Model has no values
     * @param IRequest $Request
     * @param int $intent the read intent.
     * Typically IWriteAccess::INTENT_POST, IWriteAccess::INTENT_PATCH or IWriteAccess::INTENT_DELETE.
     * Note: during IWriteAccess::INTENT_POST, the instance $Model contains no data.
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     */
    function assertWriteAccess(PDOModel $User, IRequest $Request, $intent) {
        $T = $this->mTable;
        switch($intent) {
            case IWriteAccess::INTENT_PATCH:
                $id = $T->loadBySession()->getID();    // Assert logged in
                if($User->columnValue($T::COLUMN_ID) !== $id)
                    throw new InvalidPermissionException("No permission to modify " . $User);
                break;
            case IWriteAccess::INTENT_DELETE:
                $id = $T->loadBySession()->getID();    // Assert logged in
                if(!$this->mDelete
                    || $User->columnValue($T::COLUMN_ID) != $id)
                    throw new InvalidPermissionException("No permission to delete " . $User);
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
    function assignAccessID(IRequest $Request, $intent) { }

    /**
     * Return the user model instance
     * @return \CPath\Framework\PDO\Templates\User\Model\PDOUserModel
     */
    function getUserAccount() { return $this->mTable; }
}
