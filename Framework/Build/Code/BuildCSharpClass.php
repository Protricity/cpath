<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Build\Code;

class BuildCSharpClass extends AbstractBuildCodeClass {

    const TAB = "\t\t";
    const CONST_PREFIX = "const String ";
    const PROP_PREFIX = "String ";

    private $mClassCode = array();

    public function __construct($name, $namespace) {
        parent::__construct($name, $namespace);
    }

    public function addClassCode($code = "") {
        $this->mClassCode[] = "\t" . $code . "\n";
        return $this;
    }

    public function addDataContractAttribute($name) {
        return $this->addClassCode("[DataContract(Name=\"{$name}\")]");
    }

    public function addDataMemberAttribute($name) {
        return $this->addPropertyCode("[DataMember(Name=\"{$name}\")]");
    }

    public function buildStart($namespace, $uses, $extends, Array $implements) {
        $code = "";

        if($extends) {
            if(strpos($extends, '.')) {
                $uses[] = $extends;
                $extends = substr($extends, strrpos($extends, '.')+1);
            }
        }

        foreach($implements as $implement)
            $extends .= ($extends ? ', ' : '') . $implement;

        foreach($uses as $as => $u)
            $code .= "using {$u}" . (!is_int($as) ? ' as '.$as : '') . ";\n";

        if($namespace) $code .= "\nnamespace " . $this->getNamespace() . " {\n";
        $code .= "\n";

        $code .= implode($this->mClassCode);

        $code .= "\tclass " . $this->getName(false) . ($extends ? " : " . $extends : "");
        $code .= " {\n";
        return $code;
    }

    public function buildEnd(&$code) {
        $code .= "\t}";
        $code .= "\n}";
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
            $value = "@\"" . str_replace('"', '`', $value) . "\"";
        }
        return $value;
    }
}

