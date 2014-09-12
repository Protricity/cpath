<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 1:52 PM
 */
namespace CPath\Request\CLI;

class CommandString
{
    private $mArgs = array();
    private $mArgPos = 0;

    public function __construct(Array $args) {
        $this->mArgs = self::parseArgs($args);
    }

    public function getOption($key, $default=null) {
        return isset($this->mArgs[$key]) ? $this->mArgs[$key] : $default;
    }

    public function hasOption($key) {
        return isset($this->mArgs[$key]);
    }



    public function getNextArg() {
        if (isset($this->mArgs[$this->mArgPos]))
            return $this->mArgs[$this->mArgPos++];
        return null;
    }

    // Static

    public static function parseArgs($args) {
        if(!is_array($args))
            $args = preg_split('/\s/i', $args);

        $result = array();
        for ($i = 0; $i < sizeof($args); $i++) {

            $arg = $args[$i];
            if ($arg === '')
                continue;

            if ($arg[0] == '-') {
                $val = true;
                if (!empty($args[$i + 1]) && $args[$i + 1][0] !== '-') {
                    $val = $args[++$i];
                    if($val[0] == '"') {
                        $val = substr($val, 1);
                        while(!empty($args[$i + 1])) {
                            $next = $args[++$i];
                            $val .= ' ' . $next;
                            if(strpos('"', $next) !== false)
                                break;
                        }
                    }
                }
                $result[substr($arg, 1)] = $val;

            } else {
                if($arg[0] == '"') {
                    $val = substr($arg, 1);
                    while(!empty($args[$i + 1])) {
                        $next = $args[++$i];
                        $pos = strpos('"', $next);
                        if($pos !== false) {
                            $val .= ' ' . substr($next, 0, $pos);
                            break;
                        }
                        $val .= ' ' . $next;
                    }
                    $result[] = $val;

                } else {
                    $result[] = $arg;
                }
            }
        }
        return $result;
    }

    public static function fromString($cmd) {
        return new CommandString(explode(" ", $cmd));
    }
}