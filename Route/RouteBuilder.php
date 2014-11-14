<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Route;
use CPath\Build\Editor\PHP\PHPMethodEditor;
use CPath\Build\IBuildRequest;
use CPath\Build\MethodDocBlock;
use CPath\Config;
use CPath\Request\CLI\CommandString;

class RouteBuilder {

    const BUILD_KEY = 'build';
    const BUILD_ARG = 'routes';
    const BUILD_DISABLED = 'disabled';
    const FUNC_PREG = "/Map->route\('([^']+)', (.+)\)/";
    const FUNC_PRINT = "\t\t\t\$Map->route('%s', %s)";
    const KEY_FORMAT = "\t\t\t// @group %s";

    private $mGroupKey;
    private $mRoutes = array();
    private $mGroupArgs = array();
    private $mMethod;

    /**
     * @param IBuildRequest $Request the build instance for this session
     * @param IRouteMap $Routable instance of class file to be built/modified
     * @param String|null $groupKey optional group key for added routes
     * @throws \InvalidArgumentException
     */
    public function __construct(IBuildRequest $Request, IRouteMap $Routable, $groupKey=null) {
        $Class = new \ReflectionClass(get_class($Routable));

        foreach($Class->getMethods() as $Method) {
            $DocBlock = new MethodDocBlock($Method);
            if($DocBlock->hasTag(self::BUILD_KEY)) {
                $Tag = $DocBlock->getNextTag(self::BUILD_KEY);
                $args = CommandString::parseArgs($Tag->getArgString());

                if(isset($args[0]) && $args[0] === self::BUILD_ARG) {
                    if(isset($args[self::BUILD_DISABLED]) && $args[self::BUILD_DISABLED])
                        continue;
                    if($this->mMethod)
                        throw new \InvalidArgumentException("Only one method per class may auto-generate route code in " . get_class($Routable));
                    $this->mMethod = $Method;
                }
            }

        }

        if(!$this->mMethod)
            throw new \InvalidArgumentException("No @" . self::BUILD_KEY . " doctags found in " . get_class($Routable));

        if(!$groupKey) {
            $backtrace = debug_backtrace();
            $groupKey = $backtrace[1]['class']; // . '::' . $backtrace[1]['function'];
        }
        $this->mGroupKey = $groupKey;

        $Editor = PHPMethodEditor::fromMethod($this->mMethod);


        $methodSource = $Editor->getMethodSource();
        $curKey = '';
        foreach(explode("\n", $methodSource) as $line) {
            $prefix = $argList = '';

            if(preg_match(self::FUNC_PREG, $line, $matches)) {
                $prefix = $matches[1];
                $argList = $matches[2];
                if(!isset($this->mRoutes[$curKey]))
                    $this->mRoutes[$curKey] = array();

                $this->mRoutes[$curKey][$prefix] = $argList;

            } else if(sscanf($line, self::KEY_FORMAT, $key) === 1) {
                $curKey = $key;
                list(, $cmd) = explode('@', $line);
                $this->mGroupArgs[$curKey] = CommandString::parseArgs($cmd);

            } else {
                // todo: unrecognized line

            }
        }

        //print_r($this->mRoutes);die($this->mGroupKey);
        if($Request->hasFlag($Request::IS_SESSION_BUILD)) {
            //$this->mRoutes = array();
//            foreach($this->mRoutes as $key => $group) {
//                if(isset($this->mGroupArgs[$key]) && !empty($this->mGroupArgs[$key]['custom']));
//                //else unset($this->mGroupArgs[$key], $this->mRoutes[$key]);
//            }
        } else {
            unset($this->mRoutes[$this->mGroupKey]);
        }
    }

    public function writeRoute($prefix, $target, $_arg=null) {
        $argList = '';
        for($i=1; $i<func_num_args(); $i++)
            $argList .= ($argList ? ', ' : '') . var_export(func_get_arg($i), true);

        $this->mRoutes[$this->mGroupKey][$prefix] = $argList;

        $src = "\t\treturn";

        ksort($this->mRoutes);
        foreach($this->mRoutes as $groupKey => $routes) {
            $src .= "\n" . sprintf(self::KEY_FORMAT, $groupKey);
            foreach($routes as $prefix => $argList) {
                $src .= "\n" . sprintf(self::FUNC_PRINT, $prefix, $argList) . " ||";
            }
            $src .= "\n";
        }
        $src = substr($src, 0, -4) . ";";

        $Editor = PHPMethodEditor::fromMethod($this->mMethod);
        $Editor->replaceMethodSource("\n" . $src . "\n\t");
        $Editor->write();
        return true;
    }
}