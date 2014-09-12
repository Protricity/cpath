<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 10:02 PM
 */
namespace CPath\Build;

class PHPFileEditor
{
    private $mPath;

    public function __construct($phpFilePath)
    {
        $this->mPath = $phpFilePath;
    }

    public function scanTokens($_tokens)
    {

    }

    public function getClassTokens()
    {
        $code = file_get_contents($this->mPath);
        $tokens = token_get_all($code);
        $count = count($tokens);
        $namespace = '';
        $classes = array();

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < $count; $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $namespace .= '\\' . $tokens[$j][1];
                    } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                $valueKey = null;
                $class = $tokens[$i + 2][1];
                $fullClass = $namespace . '\\' . $class;
                $values = array(
                    T_CLASS => $class,
                    T_NAMESPACE => $namespace,
                );
                for ($j = $i + 1; $j < $count; $j++) {
                    if (in_array($tokens[$j][0], array(T_EXTENDS, T_IMPLEMENTS))) {
                        $valueKey = $tokens[$j][0];
                        $values[$valueKey] = array();
                    } else if ($tokens[$j][0] === T_STRING) {
                        $values[$valueKey][] = $tokens[$j][1];
                    } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
                $classes[$fullClass] = $values;
            }
        }
        return $classes;
    }
}