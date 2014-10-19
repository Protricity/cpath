<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/17/14
 * Time: 8:17 PM
 */
namespace CPath\Cache;
/**
 * Class Cache - Provides cache storage and retrieval
 * @package CPath
 */
interface ICache
{

	/**
	 * Caches model after a fetch. To be overwritten by derived class
	 * @param String $key the key the data will be stored at
	 * @param mixed $var the data to store
	 * @param int $ttl the time to live
	 * @return boolean true on success
	 */
	function store($key, $var, $ttl = 0);

	/**
	 * Removes model from cache. To be overwritten by derived class
	 * @param String $key the key to search for
	 * @return boolean Returns TRUE on success or FALSE on failure.
	 */
	function remove($key);

	/**
	 * Attempts to check the cache for an object. To be overwritten by derived class
	 * @param String $key the key to search for
	 * @param boolean|NULL $success true if the data was found
	 * @return mixed|NULL the loaded data or NULL if not found
	 */
	function fetch($key, &$success = null);

	/**
	 * Check to see if cache is enabled
	 * @return boolean true if this cache is enabled
	 */
	static function enabled();

	/**
	 * Check to see if cache is available
	 * @return boolean true if this cache is available
	 */
	static function available();
}