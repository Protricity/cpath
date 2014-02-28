<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Build\Code;

use CPath\Framework\Build\Code\AbstractBuildClass;

class BuildPHPClass extends AbstractBuildClass {

    const TAB = "\t";
    const CONST_PREFIX = "const ";
    const PROP_PREFIX = "$";

    public function __construct($name, $namespace) {
        parent::__construct($name, $namespace);
    }

    public function export($value) {
        if(is_array($value)) {
            $i = 0;
            $php = 'array(';
            foreach($value as $k=>$v) {
                if($i) $php .= ',';
                if($i++ !== $k)
                    $php .= var_export($k, true) . '=>';
                $php .= var_export($v, true);
            }
            $php .= ')';
            $value = $php;
        } else {
            $value = var_export($value, true);
        }
        return $value;
    }

    public function buildStart($namespace, $uses, $extends, Array $implements) {
        $code = "<?php\n";
        if($namespace) $code .= "namespace " . $this->getNamespace() . ";\n";
        $code .= "\n";

        if($extends) {
            if(strpos($extends, '\\')) {
                $uses[] = $extends;
                $extends = basename($extends);
            }
            $extends = ' extends ' . $extends;
        }

        foreach($uses as $as => $u)
            $code .= "use {$u}" . (!is_int($as) ? ' as '.$as : '') . ";\n";


        $code .= "class " . $this->getName(false);
        $code .= $extends;

        foreach($implements as $i => $implement)
            $code .= (!$i ? ' implements ' : ', ') . $implement;
        $code .= " {\n";
        return $code;
    }

    public function buildEnd(&$code) {
        $code .= "}";
    }
}

