<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

/**
 * Class Profile provides profiling information on the framework
 * @package CPath
 */
abstract class Profile {
    const COUNT = 0;
    const TIME_LAST = 1;
    const TIME_AVG = 2;

    private static $mConfig;
    private static $mStart;

    static function load() {
        $path = self::getConfigPath();
        $config = array(
            'include' => array(),
        );
        if(file_exists($path))
            include ($path);
        self::$mConfig = $config;

        if(empty(self::$mConfig['load']))
            self::$mConfig['load'] = 1;
        else
            self::$mConfig['load']++;

        self::$mStart = microtime();
        self::$mConfig['time'] = NULL;
        spl_autoload_unregister(__NAMESPACE__ . '\Base::autoload');
        spl_autoload_register(__NAMESPACE__ . '\Profile::profileClass', true);
        register_shutdown_function(__NAMESPACE__.'\Profile::unload');
    }

    static function unload() {
        $etime = microtime();
        $stime = explode(' ', self::$mStart);
        $etime = explode(' ', $etime);
        $s = $etime[0] - $stime[0];
        $m = (float)$etime[1] - (float)$stime[1];
        self::$mConfig['time'] = (float)$m + (float)$s;

        arsort(self::$mConfig['include']);
        $php = <<<'PHP'
<?php
/**
  Count     Freq      Load(μs)  Class
PHP;
        $php .= "\n";
        $load = self::$mConfig['load'];
        foreach(self::$mConfig['include'] as $include=>$config)
            $php .= sprintf("  %-10d%-10d%-10d%s\n", $config[self::COUNT], $config[self::COUNT]/$load*100, $config[self::TIME_AVG] * 1000000, $include);

        $php .= <<<'PHP'
*/
$config =
PHP;

        $php .= var_export(self::$mConfig, true).";";
        $path = self::getConfigPath();
        if (!is_dir(dirname($path)))
            mkdir(dirname($path), NULL, true);
        file_put_contents($path, $php);
    }

    /**
     * Return the profile file full path
     * @return string build config full path
     */
    private static function getConfigPath() {
        static $path = NULL;
        return $path ?: $path = Config::getGenPath().'profile.gen.php';
    }

    static function profileClass($name) {
        if(empty(self::$mConfig['include'][$name])) {
            $config = self::$mConfig['include'][$name] = array(self::COUNT => 1);
        } else {
            $config = self::$mConfig['include'][$name];
            $config[self::COUNT]++;
        }
        $stime = microtime();
        Base::autoload($name);

        $etime = microtime();

        $stime = explode(' ', $stime);
        $etime = explode(' ', $etime);
        $s = $etime[0] - $stime[0];
        $m = (float)$etime[1] - (float)$stime[1];

        $config[self::TIME_LAST] = (float)$m + (float)$s;

        $lTime = isset($config[self::TIME_AVG]) ? $config[self::TIME_AVG] : $config[self::TIME_LAST];
        $config[self::TIME_AVG] = (($lTime * $config[self::COUNT]) + $config[self::TIME_LAST]) / ($config[self::COUNT] + 1);

        self::$mConfig['include'][$name] = $config;
    }

}
