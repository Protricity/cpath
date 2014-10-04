<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/23/14
 * Time: 10:25 PM
 */
namespace CPath\Build\Editor\PHP;

use CPath\Build\PHPSourceChunk;

class PHPFileEditor implements IPHPWritableSource
{
    private $mPath;
    /** @var PHPClassEditor[] */
    private $mClasses = array();
    private $mScanner;

    public function __construct($phpFilePath)
    {
        $this->mPath = realpath($phpFilePath);
        if (!$this->mPath)
            throw new \InvalidArgumentException("Invalid Path: " . $phpFilePath);

        $code = file_get_contents($this->mPath);
        $tokens = token_get_all($code);

        $Scanner = new PHPTokenScanner($tokens, $this);
        $this->mScanner = $Scanner;


        $namespace = null;
        while ($token = $Scanner->scan(T_NAMESPACE, T_CLASS)) {
            switch ($token[0]) {
                case T_NAMESPACE:
                    if (!$token = $Scanner->scan(T_STRING))
                        throw new \Exception("Namespace string could not be determined");
                    $namespace = $token[1];

                    while ($token = $Scanner->next()) {
                        if(is_array($token))
                            switch($token[0]) {
                                case T_NS_SEPARATOR:
                                case T_STRING:
                                    $namespace .= $token[1];
                                    break;
                                default:
                                    break 2;
                            }
                    }
                    break;

                case T_CLASS:
                    $start = $Scanner->getPos();
                    $openTags = 0;
                    while ($next = $Scanner->scan('{', '}')) {
                        $token = is_array($next) ? $next[0] : $next;
                        switch ($token) {
                            case '{':
                                $openTags++;
                                break;
                            case '}':
                                $openTags--;

                                if($openTags > 0)
                                    break;

                                $finish = $Scanner->getPos();
                                $ClassChunk = $Scanner->createChunk($start - 1, $finish);
                                $ClassEditor = new PHPClassEditor($ClassChunk, $namespace);
                                $this->mClasses[$ClassEditor->getClassName()] = $ClassEditor;
                                break;
                        }
                    }
                    break;
            }
        }
    }

    function getClassEditor($className) {
        if(!isset($this->mClasses[$className]))
            throw new \InvalidArgumentException("Class '{$className}' not found in file '" . $this->mPath);
        return $this->mClasses[$className];
    }

    function getClassEditors() {
        return array_values($this->mClasses);
    }

    function write() {
        $newSRC = $this->mScanner->getSourceString();
        $result = file_put_contents($this->mPath, $newSRC);
    }
}