<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders\Tools;

abstract class BuildClass  {

    const TAB = null;
    const CONST_PREFIX = null;
    const PROP_PREFIX = null;

    private
        $mName,
        $mNamespace,
        $mExtends = NULL,
        $mImplements = array(),
        $mConsts = array(),
        $mProps = array(),
        $mUse = array(),
        $mStaticProps = array(),
        $mMethods = array(),
        $mStaticMethods = array();

    public function __construct($name, $namespace) {
        $this->mName = $name;
        $this->mNamespace = $namespace;
    }

    public abstract function export($value);

    public function getName($withNamespace=true) { return $withNamespace ? $this->getNamespace() . '\\' . $this->mName : $this->mName; }
    public function getNamespace() { return $this->mNamespace; }

    public function addConst($name, $value) {
        $this->mConsts[$name] = static::TAB . static::CONST_PREFIX . $name . " = ". $this->export($value).";\n";
        return $this;
    }

    public function addConstCode($code = "") {
        $this->mConsts[] = static::TAB . $code . "\n";
        return $this;
    }

    public function addProperty($name, $value=NULL, $visibility='protected', $static=false, $export=true) {
        if($visibility) $visibility .= ' ';
        if($export && ($value !== NULL)) {
            $value = $this->export($value);
            $value = str_replace("\n", '', $value);
        }
        if($static) $visibility .= 'static ';
        $prop = static::TAB . $visibility . static::PROP_PREFIX . $name . ($value !== NULL ? ' = ' . $value : '') . ";\n";
        $static ? $this->mStaticProps[$name] = $prop : $this->mProps[$name] = $prop;
        return $this;
    }

    public function addPropertyCode($code = "", $static=false) {
        $code = static::TAB . $code . "\n";
        $static ? $this->mStaticProps[] = $code : $this->mProps[] = $code;
        return $this;
    }

    public function addStaticProperty($name, $value=NULL, $visibility='protected', $export=true) {
        return $this->addProperty($name, $value, $visibility, true, $export);
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
        if(strpos($content, "\n") !== false)
            $content = "\n" . trim($content, "\n") . "\n" . static::TAB;
        $php = static::TAB . "{$visibility}function {$name}({$params}) {{$content}}\n";
        $static ? $this->mStaticMethods[$name] = $php : $this->mMethods[$name] = $php;
        return $this;
    }

    public function hasMethod($name) {
        return !empty($this->mMethods[$name]) || !empty($this->mStaticMethods[$name]);
    }

    public function addMethodCode($code = "", $static=false) {
        $code = static::TAB . $code . "\n";
        $static ? $this->mStaticMethods[] = $code : $this->mMethods[] = $code;
        return $this;
    }

    public function addStaticMethod($name, $params, $content, $visibility='') {
        $this->addMethod($name, $params, $content, $visibility, true);
        return $this;
    }

    public function addStaticMethodCode($code = "") {
        $this->mStaticMethods[] = static::TAB . $code . "\n";
        return $this;
    }

    public function setExtend($extends) {
        $this->mExtends = $extends;
        return $this;
    }

    public function addUse($use, $as=NULL) {
        if($as) $this->mUse[$as] = $use;
        else $this->mUse[] = $use;
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

    public abstract function buildStart($namespace, $uses, $extends, Array $implements);
    public abstract function buildEnd(&$code);

    public function build() {
        $t = static::TAB;

        $code = $this->buildStart($this->mNamespace, $this->mUse, $this->mExtends, $this->mImplements);

        if($this->mConsts) {
            $code .= "\n{$t}// Constants\n";
            foreach($this->mConsts as $const)
                $code .= $const;
        }

        if($this->mProps) {
            $code .= "\n{$t}// Properties\n";
            foreach($this->mProps as $prop)
                $code .= $prop;
        }

        if($this->mStaticProps) {
            $code .= "\n{$t}// Static Properties\n";
            foreach($this->mStaticProps as $prop)
                $code .= $prop;
        }

        if($this->mMethods) {
            $code .= "\n{$t}// Methods\n";
            foreach($this->mMethods as $method)
                $code .= $method;
        }

        if($this->mStaticMethods) {
            $code .= "\n{$t}// Static Methods\n";
            foreach($this->mStaticMethods as $method)
                $code .= $method;
        }

        $this->buildEnd($code);
        return $code;
    }
}

