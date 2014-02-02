<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Predicates;

use CPath\Framework\User\Role\Common\IDebuggerRole;
use CPath\Framework\User\Role\Exceptions\NotDeveloperException;
use CPath\Framework\User\Role\Interfaces\IRoleCollection;
use CPath\Framework\User\Role\Interfaces\IRoleProfile;

class IsDebugger implements IRoleProfile {

    /**
     * Filter roles by true or false
     * @param mixed $Object
     * @return bool
     */
    function onPredicate($Object) {
        if(!$Object instanceof IDebuggerRole)
            return false;
        return true;
    }

    /**
     * Assert Role profile using found roles
     * @param \CPath\Framework\User\Role\Interfaces\IRoleCollection $FoundRoles
     * @throws NotDeveloperException if the user role assertion fails
     */
    function assert(IRoleCollection $FoundRoles) {
        if($FoundRoles->count() <= 0)
            throw new NotDeveloperException();

        $FoundRoles->assert();
    }
}
