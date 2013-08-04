<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Cache;


use CPath\Cache;

/**
 * Class None - Placeholder class for when no cache is available
 * @package CPath\Cache
 */
class None extends Cache {

    /**
     * Caches model after a fetch. To be overwritten by derived class
     * @param String $key the key the data will be stored at
     * @param mixed $var the data to store
     * @param int $ttl the time to live
     * @return boolean true on success
     */
    function store($key, $var, $ttl=0) {
        return false;
    }

    /**
     * Removes model from cache. To be overwritten by derived class
     * @param String $key the key to search for
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    function remove($key) {
        return false;
    }

    /**
     * Attempts to check the cache for an object. To be overwritten by derived class
     * @param String $key the key to search for
     * @param boolean|NULL $success true if the data was found
     * @return mixed|NULL the loaded data or NULL if not found
     */
    function fetch($key, &$success=NULL) {
        $success = false;
        return NULL;
    }

    /**
     * Check to see if cache is enabled
     * @return boolean true if this cache is enabled
     */
    function enabled() {
        return false;
    }
}