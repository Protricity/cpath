<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Cache;


use CPath\Config;
use CPath\Serializer\ISerializable;
use CPath\Serializer\Serializer;

/**
 * Class None - Placeholder class for when no cache is available
 * @package CPath\Cache
 */
class File extends Cache {

    const DATA = 0;
    const CREATED = 1;
    const TTL = 2;

    private $mCache = array();

    private $mPath, $mEnabled = true;

    public function __construct() {
        if(!$this->mPath) {
            $this->mPath = Config::getGenPath().'cache/';
            if(!file_exists($this->mPath))
                if(!mkdir($this->mPath, NULL, true))
                    $this->mEnabled = false;
            elseif(!is_dir($this->mPath))
                $this->mEnabled = false;
        }
    }

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
        return $this->mEnabled;
    }

    private function &getCache($key) {
        $data = array();
        $path = $this->getConfigPath($key);
        if(!file_exists($path))
            return $data;

        $d = null;
        include $path;

        if(isset($d[self::TTL]))
            if(($d[self::CREATED] + $d[self::TTL]) < time())
                return $data;

        return $d[self::DATA];
    }

    private function setCache($key, $data, $ttl=0) {
        $path = $this->getConfigPath($key);
        $php = "<?php\n\$d=array(";

        if(is_object($data)) {
            if($data instanceof ISerializable)
                $php .= Serializer::exportToPHPCode($data);
            else
                $php .= 'unserialize(\'' . serialize($data) . '\')';
        }
        else
            $php .= var_export($data, true);

        $php .= "," . time();
        if($ttl)
            $php .= $ttl;

        $php .= ");";
        file_put_contents($path, $php);
    }

    /**
     * Return the profile file full path
     * @param String $name the key name to search for
     * @return string build config full path
     */
    private function getConfigPath($name) {
        return $this->mPath . md5($name) . '.cache.php';
    }

}