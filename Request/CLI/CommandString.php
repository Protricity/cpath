<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 1:52 PM
 */
namespace CPath\Request\CLI;

use CPath\UnitTest\ITestable;
use CPath\UnitTest\IUnitTestRequest;

if(!defined('\CPath\Autoloader'))
    include_once(__DIR__ . "/../../Autoloader.php");

class CommandString implements ITestable
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

    public function getCommandString() {
        $cmd = '';
        foreach($this->mArgs as $key => $arg) {
            if(strpos($arg, ' ') !== false)
                $arg = '"' . $arg . '"';

            if(is_string($key))
                $cmd .= ($cmd ? ' ' : '') . '--' . $key . ($arg === true ? '' : ' ' . $arg);
            else
                $cmd .= ($cmd ? ' ' : '') . $arg;
        }
        return $cmd;
    }

    public function __toString() {
        return $this->getCommandString();
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
                if (isset($args[$i + 1]) && $args[$i + 1][0] !== '-') {
                    $val = $args[++$i];
                    if($val[0] == '"') {
                        $val = substr($val, 1);
                        while(!empty($args[$i + 1])) {
                            $next = $args[++$i];
                            $pos = strpos($next, '"');
                            if($pos !== false) {
                                $val .= ' ' . substr($next, 0, $pos);
                                break;
                            }
                            $val .= ' ' . $next;
                        }
                    }
                }
                if($arg[1] == '-')
                    $result[substr($arg, 2)] = $val;
                else
                    $result[substr($arg, 1)] = $val;

            } else {
                if($arg[0] == '"') {
                    $val = substr($arg, 1);
                    while(!empty($args[$i + 1])) {
                        $next = $args[++$i];
                        $pos = strpos($next, '"');
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

    /**
     * Perform a unit test
     * @param IUnitTestRequest $Test the unit test request inst for this test session
     * @return String|void always returns void
     * @test --disable 0
     * Note: Use doctag 'test' with '--disable 1' to have this ITestable class skipped during a build
     */
    static function handleStaticUnitTest(IUnitTestRequest $Test) {
        $cmd = 'abc --option 123 --quotes "omg value" --true';
        $args = self::fromString($cmd);
        $cmd2 = $args->getCommandString();
        if($cmd != $cmd2)
            $Test->assert(false, "[{$cmd}] != [{$cmd2}]");
    }
}

//$Test = new UnitTestRequestWrapper(new CLIMethod());
//CommandString::handleStaticUnitTest($Test);