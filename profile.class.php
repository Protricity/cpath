<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath;

use CPath\Exceptions\ValidationException;
use CPath\Interfaces\IValidate;
use CPath\Interfaces\IValidateRequest;

/**
 * Class Profile provides profiling information on the framework
 * @package CPath
 */
abstract class Profile {
    private static $mConfig;
    private static $mStart;

    static function load() {
        $path = self::getConfigPath();
        $config = array();
        if(file_exists($path))
            include ($path);
        self::$mConfig = $config;

        if(empty(self::$mConfig['load']))
            self::$mConfig['load'] = 1;
        else
            self::$mConfig['load']++;

        self::$mStart = microtime();
        self::$mConfig['time'] = NULL;

        spl_autoload_register(__NAMESPACE__.'\Profile::profileClass', true);
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
    Count   Freq    Class
PHP;
        $php .= "\n";
        $load = self::$mConfig['load'];
        foreach(self::$mConfig['include'] as $include=>$count)
            $php .= sprintf("    %-8d%-8d%s\n", $count, $count/$load*100, $include);

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
        return $path ?: $path = Config::getGenPath().'profile.php';
    }

    static function profileClass($name) {
        if(empty(self::$mConfig['include'][$name]))
            self::$mConfig['include'][$name] = 1;
        else
            self::$mConfig['include'][$name]++;
    }

}
