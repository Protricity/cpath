<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 2:55 PM
 */
namespace CPath\Build\File;

interface IPHPFileScannerCallbacks {
    function foundClass($className, &$tokens);
    function foundClassMethod($className, $methodName, &$tokens);
}

class PHPFileScanner
{
    private $mPath;
    private $mPos = 0;
    private $mTokens = null;
    private $mHandle = null;
    private $mOpen = false;

    public function __construct($filePath) {
        $this->mPath = $filePath;
    }

    function nextToken() {
//        if ($this->mTokens === null) {
//            $this->mTokens = token_get_all(file_get_contents($this->mPath));
//            return $this->nextToken();
//
//        } elseif (isset($this->mTokens[$this->mPos])) {
//            return $this->mTokens[$this->mPos++];
//
//        }
//
//        return null;
        if (isset($this->mTokens[$this->mPos])) {
            $next = $this->mTokens[$this->mPos++];
            switch($next[0]) {
                case '{':
                    $this->mOpen = true;
                    break;
                case '}':
                    $this->mOpen = false;
                    break;
            }
            return $next;
        }

        if (!$this->mHandle) {
            $this->mHandle = fopen($this->mPath, 'r');
        }
        $this->mPos = 0;
        $this->mTokens = array();

        while($buffer = fgets($this->mHandle)) {
            $this->mTokens = token_get_all('<?php ' . rtrim($buffer)); // todo; fix
            if($this->mTokens)
                return $this->nextToken();
        }

        return null;
    }

    function readString() {
        $token = $this->nextToken();
        if ($token === null)
            return null;

        if ($token[0] === T_STRING)
            return $token[1];

        if (in_array($token[0], array(T_WHITESPACE, ',', ' ')))
            return $this->readString();

        $this->mPos--;
        return null;
    }

    function scanClassTokens(IPHPFileScannerCallbacks $Callbacks=null) {
        $namespace = '';
        $className = '';
        $content = file_get_contents($this->mPath);
        $tokens = token_get_all($content);

        $i = 0;
        $readStr = function($scan=T_STRING, $skip=T_WHITESPACE) use ($tokens, &$i) {
            $str = null;
            while(isset($tokens[$i])) {
                $token = $tokens[$i++];

                if(in_array($token[0], (array)$skip))
                    continue;

                if(in_array($token[0], (array)$scan)) {
                    $str .= isset($token[1]) ? $token[1] : $token[0];
                    continue;
                }

                $i--;
                return $str;
            }
            return $str;
        };

        $results = array(
            T_USE=>array(),
            T_NAMESPACE => array(),
            T_CLASS => array()
        );

        while(isset($tokens[$i])) {
            $token = $tokens[$i++];
            if ($token[0] === T_USE) {
                $results[T_USE][] = $readStr(array(T_STRING, T_NS_SEPARATOR));

            } elseif ($token[0] === T_EXTENDS) {
                while ($value = $readStr(T_STRING, array(T_WHITESPACE, ','))) {
                    $results[T_CLASS][$className][T_EXTENDS][] = $value;
                }
            } elseif ($token[0] === T_IMPLEMENTS) {
                $value = $readStr(array(T_STRING, ','));
                $results[T_CLASS][$className][T_IMPLEMENTS] = explode(',', $value);

            } elseif ($token[0] === T_FUNCTION) {
                $value = $readStr();
                $results[T_CLASS][$className][T_FUNCTION] = $value;
                if($Callbacks)
                    $Callbacks->foundClassMethod($className, $value, $tokens);

            } elseif ($token[0] === T_CLASS) {
                if ($value = $readStr()) {
                    $className = '\\' . $namespace . '\\' . $value;
                    if (!isset($results[T_CLASS][$className]))
                        $results[T_CLASS][$className] = array(
                            T_EXTENDS => array(),
                            T_IMPLEMENTS => array(),
                            T_CLASS => $value,
                            T_NAMESPACE => $namespace,
                        );
                    if($Callbacks)
                        $Callbacks->foundClass($className, $tokens);
                }

            } elseif ($token[0] === T_NAMESPACE) {
                if ($value = $readStr(array(T_STRING, T_NS_SEPARATOR))) {
                    $namespace = $value;
                    $results[T_NAMESPACE][] = $value;
                }

            }
        }

        return $results;
    }

}