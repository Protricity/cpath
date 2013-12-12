<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Cache\APC;
use CPath\Cache\File;
use CPath\Cache\None;

/**
 * Class Cache - Provides cache storage and retrieval
 * @package CPath
 */
abstract class Cache{
    //const PREFIX = 'cpath.route';
    private static $Cache = NULL;

    public static function get() {
        if(self::$Cache)
            return self::$Cache;
        $Cache = new APC();
        if(!$Cache->enabled())
            $Cache = new File();
        if(!$Cache->enabled())
            $Cache = new None();
        return self::$Cache = $Cache;
    }


    /**
     * Caches model after a fetch. To be overwritten by derived class
     * @param String $key the key the data will be stored at
     * @param mixed $var the data to store
     * @param int $ttl the time to live
     * @return boolean true on success
     */
    abstract function store($key, $var, $ttl=0) ;

    /**
     * Removes model from cache. To be overwritten by derived class
     * @param String $key the key to search for
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    abstract function remove($key);

    /**
     * Attempts to check the cache for an object. To be overwritten by derived class
     * @param String $key the key to search for
     * @param boolean|NULL $success true if the data was found
     * @return mixed|NULL the loaded data or NULL if not found
     */
    abstract function fetch($key, &$success=NULL);

    /**
     * Check to see if cache is enabled
     * @return boolean true if this cache is enabled
     */
    abstract function enabled();
}