<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Interfaces\IBuilder;
use CPath\Models\Response;
use CPath\Handlers\Api;

class BuildException extends \Exception {}

class Build extends Api {

    const ROUTE_PATH = 'build';     // Allow manual building from command line: 'php index.php build'
    const ROUTE_METHODS = 'CLI';    // CLI only

    /**
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param array $request associative array of request Fields, usually $_GET or $_POST
     * @return \CPath\Interfaces\IResponse the api call response with data, message, and status
     */
    function execute(Array $request)
    {
        $Response = new Response(false, "Starting Build");
        $Response->startLogging();
        Build::buildClasses(true);
        return $Response
            ->update(true, "Build Complete")
            ->stopLogging();
    }

    // Statics

    /** @var $mBuilders IBuilder[] */
    private static $mBuilders = array();
    private static $mClasses = array();
    private static $mBuildConfig = NULL;
    private static $mForce = false;

    /**
     * Return the build config full path
     * @return string build config full path
     */
    private static function getConfigPath() {
        static $path = NULL;
        return $path ?: $path = Base::getGenPath().'build.php';
    }

    /**
     * Load build config data for a class
     * @param null $class optional class to load data for
     * @return mixed build config data
     */
    private static function &loadConfig($class=NULL) {
        if(self::$mBuildConfig === NULL) {
            $path = self::getConfigPath();
            $config = array();
            if(file_exists($path))
                include ($path);
            self::$mBuildConfig = $config;
        }
        if(!$class) return self::$mBuildConfig;
        if(is_object($class)) $class = get_class($class);
        if(!isset(self::$mBuildConfig[$class])) self::$mBuildConfig[$class] = array();
        return self::$mBuildConfig[$class];
    }

    /**
     * Load build config data for a class
     * @param $class string class to load data for
     * @param null $key if provided, only return data for this key
     * @return mixed build config data
     */
    public static function &getConfig($class, $key=NULL) {
        $Config =& self::loadConfig($class);
        if($key) return $Config[$key];
        return $Config;
    }

    /**
     * Set build config data for a class
     * @param $class string class to load data for
     * @param $key string data key
     * @param $val string data value
     */
    public static function setConfig($class, $key, $val) {
        $Config = self::loadConfig($class);
        $Config[$key] = $val;
    }

    /**
     * Commit the build config to the file
     */
    public static function commitConfig() {
        $config = self::loadConfig();
        $php = "<?php\n\$config=".var_export($config, true).";";
        $path = self::getConfigPath();
        file_put_contents($path, $php);
    }

    /**
     * Returns true if the current build should be forced
     * @return bool whether or not to force build
     */
    public static function force() { return self::$mForce; }

    /**
     * Return the build config data from the build file if it exists
     * @return array the build config data
     */
    public static function buildConfig() {
        $config = array(
            'debug' => false,
            'build' => false,
            'db-upgrade' => false,
            'domain' => 'http://'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : gethostname()).'/',
        );
        $php = "<?php\n\$config=".var_export($config, true).";";
        $path = Base::getGenPath().'config.php';
        file_put_contents($path, $php);
        return $config;
    }

    /**
     * Build all classes
     */
    public static function buildClasses($force=false) {
        Log::v(__CLASS__, "Starting Builds");
        self::$mForce = $force;
        self::$mBuilders = array();
        self::findClass(dirname(__DIR__), '');
        /** @var $Class \ReflectionClass */
        $exCount = 0;
        foreach(self::$mClasses as $Class) {
            /** @var $Builder IBuilder */
            foreach(self::$mBuilders as $Builder) try {
                $Builder->build($Class);
            } catch (\Exception $ex) {
                $exCount++;
                Log::ex(get_class($Builder), $ex);
            }
        }
        /** @var $Builder IBuilder */
        foreach(self::$mBuilders as $Builder)
            $Builder->buildComplete();
        self::commitConfig();
        if($exCount)
            throw new \Exception("Build Failed: {$exCount} Exception(s) Occurred");
        Log::v(__CLASS__, "All Builds Complete");
    }

    /**
     * Find all classes by folder
     * @param $path string the class path
     * @param $dirClass string the class namespace
     * @throws \Exception if a build fails
     */
    private static function findClass($path, $dirClass) {
        if(file_exists($path.'/.buildignore'))
            return;
        foreach(scandir($path) as $file) {
            if(in_array($file, array('.', '..')))
                continue;
            $filePath = $path . '/' . $file;
            if(is_dir($filePath)) {
                self::findClass($filePath, $dirClass .'\\'. ucfirst($file));
                continue;
            }
            if(strcasecmp(substr($file, -10), '.class.php') !== 0)
                continue;
            $name = substr($file, 0, strlen($file) - 10);
            $class = $dirClass . '\\' . ucfirst($name);
            require_once($filePath);
            $Class = new \ReflectionClass($class);
            if($Class === NULL)
                throw new \Exception("Class '{$class}' not found in '{$filePath}'");

            if($Class->getConstant('BUILD_IGNORE')) {
                Log::v(__CLASS__, "Ignoring Class '{$class}' in '{$filePath}'");
                continue;
            }
            if($Class->isAbstract()) {
                //Log::v(__CLASS__, "Ignoring Abstract Class '{$class}' in '{$filePath}'");
                continue;
            }
            static::$mClasses[] = $Class;

            if($Class->implementsInterface(__NAMESPACE__."\Interfaces\IBuilder")) {
                Log::v(__CLASS__, "Found Builder Class '{$class}' in '{$filePath}'");
                //call_user_func(array($Class->getName(), 'build'), $Class);
                static::$mBuilders[] = $Class->newInstance();
            }
        }
    }
}