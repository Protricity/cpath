<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/18/14
 * Time: 12:58 PM
 */
namespace CPath\Data\Map;

interface ISequenceMapper
{
    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @param mixed $_arg additional varargs
     * @return bool true to stop or any other value to continue
     */
    function mapNext($value, $_arg = null);
}