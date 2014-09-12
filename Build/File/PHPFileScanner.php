<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 2:55 PM
 */
namespace CPath\Build\File;

class PHPFileScanner
{
    private $mPath;
    private $mHandle = null;
    private $mPos = 0;
    private $mTokens = array();

    public function __construct($filePath) {
        $this->mPath = $filePath;
    }

    function nextToken() {
        if ($this->mTokens === null) {
            return null;
        } elseif (isset($this->mTokens[$this->mPos])) {
            return $this->mTokens[$this->mPos++];
        }

        if (!$this->mHandle) {
            $this->mHandle = fopen($this->mPath, 'r');
        }
        $this->mPos = 0;
        $this->mTokens = array();

        $buffer = fgets($this->mHandle);
        if (!$buffer) {
            return null;
        }

        $this->mTokens = token_get_all($buffer) ? : null;
        return $this->nextToken();
    }

    function readString() {
        $token = $this->nextToken();
        if ($token === null)
            return null;

        if ($token[0] === T_STRING)
            return $token[1];

        if ($token[0] === T_WHITESPACE)
            return $this->readString(true);

        $this->mPos--;
        return null;
    }

    function scanClassTokens() {
        $namespace = '';
        $className = '';
        $classes = array();
        while ($token = $this->nextToken()) {
            if ($token[0] === T_EXTENDS) {
                while ($value = $this->readString()) {
                    $classes[$className][T_EXTENDS][] = $value;
                }
            } elseif ($token[0] === T_IMPLEMENTS) {
                while ($value = $this->readString()) {
                    $classes[$className][T_IMPLEMENTS][] = $value;
                }
            } elseif ($token[0] === T_CLASS) {
                if ($value = $this->readString()) {
                    $className = $namespace . '/' . $value;
                    if (!isset($classes[$className]))
                        $classes[$className] = array(
                            T_EXTENDS => array(),
                            T_IMPLEMENTS => array(),
                            T_CLASS => $value,
                            T_NAMESPACE => $namespace,
                        );
                }
            } elseif ($token[0] === T_NAMESPACE) {
                if ($value = $this->readString()) {
                    $namespace = $value;
                    if (!isset($classes[$namespace]))
                        $classes[$namespace] = array();
                }
            }
        }

        return $classes;
    }

}