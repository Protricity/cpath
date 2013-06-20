<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

class BuildException extends \Exception {}

class Build {

    private static $mBuildClasses = array();
    private static $mBuildConfig = NULL;

    private static function getConfigPath() {
        static $path = NULL;
        return $path ?: $path = Base::getGenPath().'build.php';
    }

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

    public static function &getConfig($class, $key=NULL) {
        $Config =& self::loadConfig($class);
        if($key) return $Config[$key];
        return $Config;
    }

    public static function setConfig($class, $key, $val) {
        $Config = self::loadConfig($class);
        $Config[$key] = $val;
    }

    public static function commitConfig() {
        $config = self::loadConfig();
        $php = "<?php\n\$config=".var_export($config, true).";";
        $path = self::getConfigPath();
        file_put_contents($path, $php);
    }

    public static function force() { return false; }

    public static function buildConfig() {
        $config = array(
            'debug' => false,
            'build' => false,
            'domain' => 'http://'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : gethostname()).'/',
        );
        $php = "<?php\n\$config=".var_export($config, true).";";
        $path = Base::getGenPath().'config.php';
        file_put_contents($path, $php);
        return $config;
    }

    public static function buildClasses() {
        Log::v("Starting Builds");
        self::$mBuildClasses = array();
        self::buildClass(dirname(__DIR__), '');
        /** @var $Class \ReflectionClass */
        foreach(self::$mBuildClasses as $Class)
            call_user_func(array($Class->getName(), 'buildComplete'), $Class);
        self::commitConfig();
        Log::v("All Builds Complete");
    }

    private static function buildClass($path, $dirClass) {
        if(file_exists($path.'/.buildignore'))
            return;
        $Exceptions = array();
        foreach(scandir($path) as $file) {
            if(in_array($file, array('.', '..')))
                continue;
            $filePath = $path . '/' . $file;
            if(is_dir($filePath)) {
                self::buildClass($filePath, $dirClass .'\\'. ucfirst($file));
                continue;
            }
            if(strcasecmp(substr($file, -10), '.class.php') !== 0)
                continue;
            $name = substr($file, 0, strlen($file) - 10);
            $class = $dirClass . '\\' . ucfirst($name);
            try {
                require_once($filePath);
                $Class = new \ReflectionClass($class);
                if($Class === NULL)
                    throw new \Exception("Class '{$class}' not found in '{$filePath}'");

                if($Class->getConstant('BUILD_IGNORE')) {
                    Log::v("Ignoring Class '{$class}' in '{$filePath}'");
                    continue;
                }
                if($Class->isAbstract()) {
                    Log::v("Ignoring Abstract Class '{$class}' in '{$filePath}'");
                    continue;
                }

                if($Class->implementsInterface(__NAMESPACE__."\Interfaces\IBuilder")) {
                    Log::v("Building Class '{$class}' in '{$filePath}'");
                    call_user_func(array($Class->getName(), 'build'), $Class);
                    static::$mBuildClasses[] = $Class;
                }
            }
            catch (\Exception $ex) {
                $Exceptions[] = $ex;
            }
        }

        foreach($Exceptions as $ex)
            throw $ex;
    }
}