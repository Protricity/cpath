<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Role\Common;

use CPath\Framework\User\Role\Exceptions\InvalidRoleException;
use CPath\Framework\User\Role\Interfaces\IRole;
use CPath\Framework\User\Role\Interfaces\IRoleCollection;
use CPath\Framework\User\Role\Interfaces\IRoleProfile;
use CPath\Model\ArrayObject;
use CPath\Type\Collection\IPredicate;

class RoleList extends ArrayObject implements IRoleCollection {

    /** @var IRole[] array  */
    private $mList = array();

    function __construct() {

    }

    /**
     * Filter the item collection by an IPredicate
     * @param IPredicate $Where
     * @return IRoleCollection
     */
    function where(IPredicate $Where) {
        $list = array();
        foreach($this->mList as $Role)
            if($Where->onPredicate($Role) === true)
                $list[] = $Role;

        $Inst = new RoleList();
        foreach($list as $Role)
            $Inst->add($Role);

        return $Inst;
    }

    /**
     * Add an IRole to the collection
     * @param IRole $Role
     * @return IRoleCollection return self
     */
    function add(IRole $Role) {
        $this->mList[] = $Role;
    }

    /**
     * Assert a user role and return the result as a boolean
     * @param IRoleProfile $Profile the IRoleProfile to assert
     * @return bool true if the role exists and the assertion passed
     */
    function has(IRoleProfile $Profile) {
        return $this->where($Profile)->count() >= 1;
    }

    /**
     * Calls ->assert on all roles in the collection
     * @param IRoleProfile|null $Profile optional profile to assert
     * @throws InvalidRoleException if the user role assertion fails
     * @return void
     */
    function assert(IRoleProfile $Profile = null) {
        $List = $this->where($Profile);
        $Profile->assert($List);
    }

    /**
     * Return a reference to this object's associative array
     * @return array the associative array
     */
    protected function &getArray() {
        return $this->mList;
    }
}
