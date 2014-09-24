<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/23/14
 * Time: 10:25 PM
 */
namespace CPath\Build\Editor\PHP;

use CPath\Build\Editor;
use CPath\Build\Editor\PHP\PHPTokenScanner;

class PHPClassEditor
{
    private $mScanner;
    private $mNamespace;
    private $mClassName;
    private $mExtends = array();
    private $mImplements = array();
    private $mMethods = array();

    public function __construct(PHPTokenScanner $Scanner, $namespace)
    {
        $this->mNamespace = $namespace;
        $this->mScanner = $Scanner;

        $next = $Scanner->scan(T_CLASS);
        if (!$next)
            throw new \Exception("Class token could not be determined");

        while ($token = $Scanner->scan(T_STRING, T_NS_SEPARATOR, T_IMPLEMENTS, T_OPEN_TAG)) {
            if (in_array($token[0], array(T_NS_SEPARATOR, T_STRING)))
                $this->mClassName .= $token[1];
            else
                break;
        }
        $Scanner->reset();


        if (!$this->mClassName)
            throw new \Exception("Class name could not be determined");

        while ($token = $Scanner->scan(T_EXTENDS)) {
            while ($token = $Scanner->scan(T_STRING, T_IMPLEMENTS, T_OPEN_TAG)) {
                if ($token[0] === T_STRING)
                    $this->mExtends[] = $token[1];
                else
                    break;
            }
        }
        $Scanner->reset();


        while ($token = $Scanner->scan(T_IMPLEMENTS)) {
            while ($token = $Scanner->scan(T_STRING, T_OPEN_TAG)) {
                if ($token[0] === T_STRING)
                    $this->mImplements[] = $token[1];
                else
                    break;
            }
        }
        $Scanner->reset();


        $modifiers = array();
        while ($next = $Scanner->scan(T_FUNCTION, T_PRIVATE, T_PROTECTED, T_PUBLIC, T_STATIC)) {
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

                    $next = $Scanner->scan(T_OPEN_TAG);
                    if (!$next)
                        throw new \Exception("Method open tag could not be determined");
                    $start = $Scanner->getPos() + 1; // after {

                    $openTags = 1;
                    while ($next = $Scanner->scan(T_OPEN_TAG, T_CLOSE_TAG)) {
                        switch ($next[0]) {
                            case T_OPEN_TAG:
                                $openTags++;
                                break;
                            case T_CLOSE_TAG:
                                $openTags--;
                                if ($openTags > 0)
                                    continue;

                                $finish = $Scanner->getPos();

                                if (!($ClassChunk = $Scanner->createChunk($start, $finish - 1))) // before }
                                    throw new \Exception("Could not determine Class end bracket");

                                $this->mMethods[$methodName] = new Editor\PHP\PHPMethodEditor($ClassChunk, $this->mClassName, $methodName, $modifiers);
                                break 2;
                        }
                    }

                    if ($openTags !== 0)
                        throw new \Exception("Could not determine Class brackets");
                    $modifiers = array();
            }
        }

    }

    function getClassName()
    {
        return $this->mClassName;
    }

    function getSourceString()
    {
        return $this->mScanner->getSourceString();
    }

    function getMethodEditor($methodName)
    {
        if (!isset($this->mMethods[$methodName]))
            throw new \InvalidArgumentException("Method name does not exist: (" . $this->mClassName . "::)" . $methodName);

        return $this->mMethods[$methodName];
    }

    function getMethodEditors()
    {
        return array_values($this->mMethods);
    }

}