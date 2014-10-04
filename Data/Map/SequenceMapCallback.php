<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 1:16 AM
 */
namespace CPath\Data\Map;

use CPath\Render\HTML\Attribute;

class SequenceMapCallback implements ISequenceMapper
{
    private $mCallback;

    function __construct(\Closure $callback) {
        $this->mCallback = $callback;
    }

    /**
     * Map a sequential value to this map. If method returns true, the sequence should abort and no more values should be mapped
     * @param String|Array|IKeyMap|ISequenceMap $value
     * @param mixed $_arg additional varargs
     * @return bool false to continue, true to stop
     */
    function mapNext($value, $_arg = null) {
        $args = func_get_args();
        return call_user_func_array($this->mCallback, $args);
    }
}
