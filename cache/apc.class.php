<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Cache;

use CPath\Config;

/**
 * Class APC - Implements APC cache
 * @package CPath\Cache
 */
class APC extends Cache {

    /**
     * Caches model after a fetch. To be overwritten by derived class
     * @param String $key the key the data will be stored at
     * @param mixed $var the data to store
     * @param int $ttl the time to live
     * @return boolean true on success
     */
    function store($key, $var, $ttl=0) {
        return apc_store($key, $var, $ttl);
    }

    /**
     * Removes model from cache. To be overwritten by derived class
     * @param String $key the key to search for
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    function remove($key) {
        return apc_delete($key);
    }

    /**
     * Attempts to check the cache for an object. To be overwritten by derived class
     * @param String $key the key to search for
     * @param boolean|NULL $success true if the data was found
     * @return mixed|NULL the loaded data or NULL if not found
     */
    function fetch($key, &$success=NULL) {
        return apc_fetch($key, $success);
    }

    /**
     * Check to see if cache is enabled
     * @return boolean true if this cache is enabled
     */
    function enabled() {
        return Config::$APCEnabled;
    }
}