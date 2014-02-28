<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Serialize\Interfaces;

interface ISerializable { // TODO: necessary? yes, I think.

    /**
     * EXPORT Object to a simple data structure to be used in var_export($data, true)
     * @return mixed
     */
    function serialize();

    /**
     * Unserialize and instantiate an Object with the stored data
     * @param mixed $data the exported data
     * @return ISerializable|Object
     */
    static function unserialize($data);
}