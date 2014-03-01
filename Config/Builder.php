<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Config;

use CPath\Config;
use CPath\Interfaces\IConfig;
use CPath\Log;

class Builder {
    private $mConfig, $mPath, $mOverwrite;

    function __construct(IConfig $Config, $path, $overwrite=false) {
        $this->mConfig = $Config;
        $this->mPath = $path;
        $this->mOverwrite = $overwrite;
    }

    function commit() {
        if(!file_exists($this->mPath) || $this->mOverwrite) {
            $php = "<?php\n";
        } else {
            $php = file_get_contents($this->mPath);
        }

        $Class = new \ReflectionClass($this->mConfig);
        $ns = $Class->getNamespaceName();
        $vars = get_class_vars($Class->getName());
        $commit = array();

        foreach($vars as $key=>$value) {
            if($value !== NULL)
                $commit[$key] = $value;
        }
        if(!$commit) {
            Log::v($Class->getName(), "No changes for commit");
            return;
        }


        $php .= "\n// " . $Class->getName();
        $php .= "\nnamespace {$ns} {\n";
        foreach($commit as $name => $value)
            $php .= "\t" . basename($Class->getName()) . "::$" . $name . " = " . var_export($value, true) . ";\n";
        $php .= "}\n";

        file_put_contents($this->mPath, $php);
        Log::v($Class->getName(), "Committed (%d) values for %s", sizeof($commit), $Class->getName());
    }
}