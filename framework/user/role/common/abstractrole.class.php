<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\User\Role\Common;

use CPath\Framework\User\Role\Interfaces\IRole;


abstract class AbstractRole implements IRole {

    /**
     * EXPORT Object to a simple data structure to be used in var_export($data, true)
     * @return String
     */
    function serialize() {
        return json_encode($this);
    }

    /**
     * Unserialize and instantiate an Object with the stored data
     * @param mixed $data the exported data
     * @return \CPath\Framework\Data\Serialize\Interfaces\ISerializable|AbstractRole
     */
    static function unserialize($data) {
        $data = json_decode($data, true);

        $Inst = new static;
        foreach($data as $k=>$v)
            $Inst->$k = $v;

        return $Inst;
    }
}
