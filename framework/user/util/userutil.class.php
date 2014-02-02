<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Util;


use CPath\Describable\Describable;
use CPath\Framework\User\Interfaces\IUser;
use CPath\Framework\User\Role\Interfaces\IRole;
use CPath\Framework\User\Role\Interfaces\IRoleCollection;
use CPath\Framework\User\Role\Interfaces\IRoleProfile;
use CPath\Framework\User\Role\InvalidRoleException;
use CPath\Framework\User\Role\RoleException;

class UserUtil implements IUserUtil {
    private $mUser;
    /** @var IRoleCollection null  */
    private $mRoles = null;

    function __construct(IUser $User) {
        $this->mUser = $User;
    }

    /**
     * Get User ID
     * @return mixed
     */
    function getID() { return $this->mUser->getID(); }

    /**
     * Get Username
     * @return String
     */
    function getUsername() { return $this->mUser->getUsername(); }


    /**
     * Get User Email Address
     * @return String
     */
    function getEmail() { return $this->mUser->getEmail(); }

    /**
     * Load all user roles
     * @return IRoleCollection|IRole[]
     */
    function loadUserRoles() {
        return $this->mRoles
            ?: $this->mRoles = $this->mUser->loadUserRoles();
    }



    /**
     * Assert a user role and return the result as a boolean
     * @param IRoleProfile $Profile the role profile to assert
     * @return bool true if the role exists and the assertion passed
     */
    function hasRole(IRoleProfile $Profile) {
        /** @var IRole $Role */
        foreach($this->mRoles as $Role) {
            try {
                if($Profile->onPredicate($Role) === true)
                    return true;
            } catch (RoleException $ex) {

            }
        }
        return false;
    }

    /**
     * Assert a user role or throw an InvalidRoleException
     * @param IRoleProfile $Profile the role profile to assert
     * @throws InvalidRoleException if the user role assertion fails
     */
    function assertRole(IRoleProfile $Profile) {
        /** @var \CPath\Framework\User\Role\Interfaces\IRole $Role */
        foreach($this->mRoles as $Role) {
            if($Profile->onPredicate($Role) === true) {
                $Role->assert();
                return;
            }
        }

        throw new InvalidRoleException("Role profile not found: " . Describable::get($Profile)->getTitle());
    }
}