<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders\Tools;

class BuildPHPClass extends BuildClass {

    const TAB = "\t";
    const CONST_PREFIX = "const ";
    const PROP_PREFIX = "$";

    public function __construct($name) {
        parent::__construct($name);
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
        if($namespace) $code .= "namespace " . $this->Namespace . ";\n";
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


        $code .= "class " . $this->Name;
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

