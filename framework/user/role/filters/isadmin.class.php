<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Role;

use CPath\Framework\User\Role\Common\IAdminRole;
use CPath\Type\Collection\ICollectionFilter;
use CPath\Type\Collection\ICollectionItem;

class IsAdmin implements ICollectionFilter {

    /**
     * @param ICollectionItem $Item
     * @return bool
     */
    function filterItem(ICollectionItem $Item) {
        if(!$Item instanceof IAdminRole)
            return false;
        $Item->assert() TODO: decide assert vs assert(IRole)
    }
}
