<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders\Tools;

class BuildPHPClass  {
    public $Name,
        $Namespace,
        $mUse = array(),
        $mExtends = NULL,
        $mImplements = array(),
        $mConsts = array(),
        $mProps = array(),
        $mMethods = array(),
        $mStaticMethods = array();

    public function __construct($name) {
        $this->Name = $name;
    }

    public function addConst($name, $value) {
        $this->mConsts[$name] = "\tconst " . $name . " = ". var_export($value, true).";\n";
        return $this;
    }

    public function addConstCode($code = "") {
        $this->mConsts[] = "\t" . $code . "\n";
        return $this;
    }

    public function addProperty($name, $value=NULL, $visibility='protected') {
        if($visibility) $visibility .= ' ';
        $this->mProps[$name] = "\t{$visibility}\${$name}" . ($value !== NULL ? var_export($value, true) : '') . ";\n";
        return $this;
    }

    public function addPropertyCode($code = "") {
        $this->mProps[] = "\t" . $code . "\n";
        return $this;
    }

    public function addMethod($name, $params, $content, $visibility='', $static=false) {
        if(is_array($params)) {
            $p = '';
            foreach($params as $param) {
                if(strpos($param[0], '$') === false)
                    $param = '$' . $param;
                $p .= ($p ? ', ' : '') . $param;
            }
            $params = $p;
        }
        if($visibility) $visibility .= ' ';
        if($static) $visibility .= 'static ';
        $php = "\t{$visibility}function {$name}({$params}) {{$content}}" . "\n";
        $static ? $this->mStaticMethods[$name] = $php : $this->mMethods[$name] = $php;
        return $this;
    }

    public function addMethodCode($code = "") {
        $this->mMethods[] = "\t" . $code . "\n";
        return $this;
    }

    public function addStaticMethod($name, $params, $content, $visibility='') {
        $this->addMethod($name, $params, $content, $visibility, true);
        return $this;
    }

    public function addStaticMethodCode($code = "") {
        $this->mStaticMethods[] = "\t" . $code . "\n";
        return $this;
    }

    public function addUse($use, $as=NULL) {
        if($as) $this->mUse[$as] = $use;
        else $this->mUse[] = $use;
        return $this;
    }

    public function setExtend($extends) {
        $this->mExtends = $extends;
        return $this;
    }

    public function addImplements($implements) {
        if(strpos($implements, '\\')) {
            $this->addUse($implements);
            $implements = basename($implements);
        }
        $this->mImplements[] = $implements;
        return $this;
    }

    public function build() {
        $php = "<?php\n";
        if($this->Namespace) $php .= "namespace " . $this->Namespace . ";\n";
        $php .= "\n";

        $use = $this->mUse;
        if($extends = $this->mExtends) {
            if(strpos($extends, '\\')) {
                $use[] = $extends;
                $extends = basename($extends);
            }
            $extends = ' extends ' . $extends;
        }

        foreach($use as $as => $use)
            $php .= "use {$use}" . (!is_int($as) ? ' as '.$as : '') . ";\n";

        $php .= "class " . $this->Name;
        $php .= $extends;

        foreach($this->mImplements as $i => $implement)
            $php .= (!$i ? ' implements ' : ', ') . $implement;
        $php .= " {\n";

        $php .= "\n\t// Constants\n";

        foreach($this->mConsts as $const)
            $php .= $const;

        $php .= "\n\t// Properties\n";

        foreach($this->mProps as $prop)
            $php .= $prop;

        $php .= "\n\t// Methods\n";

        foreach($this->mMethods as $method)
            $php .= $method;

        $php .= "\n\t// Static Methods\n";

        foreach($this->mStaticMethods as $method)
            $php .= $method;

        $php .= "}";
        return $php;
    }
}

