<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 11:12 PM
 */
namespace CPath\Backend;

use CPath\Build\BuildRequest;
use CPath\Build\File;
use CPath\Build\IBuildRequest;
use CPath\Build\IBuildable;
use CPath\Build\MethodDocBlock;
use CPath\Request\CLI\CommandString;
use CPath\Request\IRequest;
use CPath\Request\IStaticRequestHandler;
use CPath\Route\RouteBuilder;

class BuildRequestHandler implements IStaticRequestHandler, IBuildable
{

    /**
     * Handle this request and render any content
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function handleStaticRequest(IRequest $Request) {
        $flags = 0;

        $test = $Request->prompt("Skip commit? (Test mode)", 't,test', false);
        if ($test && !in_array($test, array('n', 'N', '0')))
            $flags |= IBuildRequest::TEST_MODE;

        $skipPrompt = $Request->prompt("Use Defaults? (Skip prompt)", 'f,defaults', false);
        if ($skipPrompt && !in_array($skipPrompt, array('n', 'N', '0')))
            $flags |= IBuildRequest::USE_DEFAULTS;

        $flags |= IBuildRequest::IS_SESSION_BUILD;

        $BuildRequest = new BuildRequest($Request, $flags);
        $this->buildAllFiles($BuildRequest);
    }

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request instance for this build session
     * @return String|void always returns void
     */
    function buildAllFiles(IBuildRequest $Request) {
        $Iterator = new File\Iterator\PHPFileIterator('/', '/');

        $buildableClasses = array();
        while ($file = $Iterator->getNextFile()) {
            $Scanner = new File\PHPFileScanner($file);

            foreach ($Scanner->scanClassTokens() as $fullClass => $tokens) {
                foreach ($tokens[T_IMPLEMENTS] as $implements) {
                    if (strpos($implements, 'IBuildable') !== false) {
                        $buildableClasses[] = $fullClass;
                    }
                }
            }
        }

        foreach ($buildableClasses as $class) {
            if (class_exists($class, true)) {
                $Class = new \ReflectionClass($class);
                if ($Class->implementsInterface('\CPath\Build\IBuildRequestHandler')) {
                    $Method = $Class->getMethod('handleStaticBuild');
                    $MethodDoc = new MethodDocBlock($Method);
                    if($Tag = $MethodDoc->getNextTag('build')) {
                        $args = CommandString::parseArgs($Tag->getArgString());
                        $disabled = isset($args['disable']) && $args['disable'];
                        if($disabled) {
                            continue;
                            // TODO
                        }
                    }
                    $Method->invoke(null, $Request);
                } else {
                    // TODO
                }
            } else {
                // TODO
            }
        }
    }

    // Static

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request instance for this build session
     * @return String|void always returns void
     */
    static function handleStaticBuild(IBuildRequest $Request) {
        $Builder = new RouteBuilder($Request, new CPathBackendRoutes());
        $Builder->writeRoute('CLI /build', __CLASS__);
    }

    static function cls() {
        return __CLASS__;
    }
}