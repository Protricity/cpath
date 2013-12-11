<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Cache;


use CPath\Cache;
use CPath\Config;

/**
 * Class None - Placeholder class for when no cache is available
 * @package CPath\Cache
 */
class File extends Cache {

    const DATA = 0;
    const CREATED = 1;
    const TTL = 2;

    private $mCache = array();

    /**
     * Caches model after a fetch. To be overwritten by derived class
     * @param String $key the key the data will be stored at
     * @param mixed $var the data to store
     * @param int $ttl the time to live
     * @return boolean true on success
     */
    function store($key, $var, $ttl=0) {
        $this->setCache($key, $var, $ttl);
        //$this->mCache[$key] = $var;
        return true;
    }

    /**
     * Removes model from cache. To be overwritten by derived class
     * @param String $key the key to search for
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    function remove($key) {
        if(isset($this->mCache[$key])) {
            unset($this->mCache[$key]);
            return true;
        }
        return false;
    }

    /**
     * Attempts to check the cache for an object. To be overwritten by derived class
     * @param String $key the key to search for
     * @param boolean|NULL $success true if the data was found
     * @return mixed|NULL the loaded data or NULL if not found
     */
    function fetch($key, &$success=NULL) {
        if(isset($this->mCache[$key])) {
            $success = true;
            return $this->mCache[$key];
        }
        $data = $this->getCache($key);
        $success = true;
        return $data;
    }

    /**
     * Check to see if cache is enabled
     * @return boolean true if this cache is enabled
     */
    function enabled() {
        return true;
    }

    private function &getCache($key) {
        $data = array();
        $path = $this->getConfigPath($key);
        if(!file_exists($path))
            return $data;

        $arr = null;
        include $path;

        if(isset($arr[self::TTL]))
            if(($arr[self::CREATED] + $arr[self::TTL]) < time())
                return $data;

        return $arr[self::DATA];
    }

    private function setCache($key, $data, $ttl=0) {
        $path = $this->getConfigPath($key);
        $arr = array (
            self::DATA => $data,
            self::CREATED => time(),
        );
        if($ttl)
            $arr[self::TTL] = $ttl;

        if(is_object($data))
            $export = 'unserialize(\'' . serialize($arr) . '\')';
        else
            $export = var_export($arr, true);
        $php = "<?php\n\$arr=".$export.";";
        file_put_contents($path, $php);
    }

    /**
     * Return the profile file full path
     * @param String $name the key name to search for
     * @return string build config full path
     */
    private function getConfigPath($name) {
        static $path = NULL;
        if(!$path) {
            $path = Config::getGenPath().'cache/';
            if(!file_exists($path))
                mkdir($path, NULL, true);
        }
        return $path . md5($name) . '.cache.php';
    }

}