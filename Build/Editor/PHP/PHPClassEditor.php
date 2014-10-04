<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/23/14
 * Time: 10:25 PM
 */
namespace CPath\Build\Editor\PHP;

use CPath\Build\Editor;

class PHPClassEditor
{
    private $mScanner;
    private $mNamespace;
    private $mClassName;
    private $mExtends = array();
    private $mImplements = array();
    private $mMethods = array();

    public function __construct(PHPTokenScanner $Scanner, $namespace) {
        $this->mNamespace = $namespace;
        $this->mScanner = $Scanner;

        $next = $Scanner->scan(T_CLASS);
        if (!$next)
            throw new \Exception("Class token could not be determined");

        while ($token = $Scanner->scan(T_STRING, T_NS_SEPARATOR, T_IMPLEMENTS, '{')) {
            if (in_array($token[0], array(T_NS_SEPARATOR, T_STRING)))
                $this->mClassName .= $token[1];
            else
                break;
        }
        $Scanner->reset();


        if (!$this->mClassName)
            throw new \Exception("Class name could not be determined");

        while ($token = $Scanner->scan(T_EXTENDS)) {
            while ($token = $Scanner->scan(T_STRING, T_IMPLEMENTS, '{')) {
                if ($token[0] === T_STRING)
                    $this->mExtends[] = $token[1];
                else
                    break;
            }
        }
        $Scanner->reset();


        while ($token = $Scanner->scan(T_IMPLEMENTS)) {
            while ($token = $Scanner->scan(T_STRING, '{')) {
                if ($token[0] === T_STRING)
                    $this->mImplements[] = $token[1];
                else
                    break;
            }
        }
        $Scanner->reset();


        $modifiers = array();
        while ($next = $Scanner->scan(T_FUNCTION, T_PRIVATE, T_PROTECTED, T_PUBLIC, T_STATIC)) {
            if(is_array($next)) {
                switch ($next[0]) {
                    case T_PRIVATE:
                    case T_PROTECTED:
                    case T_PUBLIC:
                    case T_STATIC:
                        $modifiers[] = $next[0];
                        break;
                    case T_FUNCTION:
                        $next = $Scanner->scan(T_STRING);
                        if (!$next)
                            throw new \Exception("Method name could not be determined");
                        $methodName = $next[1];

                        $next = $Scanner->scan('{');
                        if (!$next)
                            throw new \Exception("Method open tag could not be determined");
                        $start = $Scanner->getPos();

                        $openTags = 1;
                        while ($next = $Scanner->scan('{', '}')) {
                            switch ($next[0]) {
                                case '{':
                                    $openTags++;
                                    break;
                                case '}':
                                    $openTags--;
                                    if ($openTags > 0)
                                        continue;

                                    $finish = $Scanner->getPos();

                                    $ClassChunk = $Scanner->createChunk($start, $finish - 1);
                                    $src = $ClassChunk->getSourceString();

                                    $this->mMethods[$methodName] = new PHPMethodEditor($ClassChunk, $this->mClassName, $methodName, $modifiers);
                                    break 2;
                            }
                        }

                        if ($openTags !== 0)
                            throw new \Exception("Could not determine Class brackets");
                        $modifiers = array();
                }
            }
        }

    }

    function getClassName($withNS=true) {
        return $withNS ? $this->mNamespace . '\\' . $this->mClassName : $this->mClassName;
    }

    function getSourceString() {
        return $this->mScanner->getSourceString();
    }

    function getMethodEditor($methodName) {
        if (!isset($this->mMethods[$methodName]))
            throw new \InvalidArgumentException("Method name does not exist: " . $this->mClassName . "::" . $methodName);

        return $this->mMethods[$methodName];
    }

    function getMethodEditors() {
        return array_values($this->mMethods);
    }

}