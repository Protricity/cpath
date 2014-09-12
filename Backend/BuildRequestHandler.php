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
use CPath\Request\IRequest;
use CPath\Request\IRequestHandler;
use CPath\Route\RouteBuilder;

class BuildRequestHandler implements IRequestHandler, IBuildable
{

    /**
     * Handle this request and render any content
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function handleRequest(IRequest $Request) {
        $flags = 0;

        $test = $Request->prompt('t,test', "Skip commit? (Test mode)", false);
        if ($test && !in_array($test, array('n', 'N', '0')))
            $flags |= IBuildRequest::TEST_MODE;

        $skipPrompt = $Request->prompt('f,defaults', "Use Defaults? (Skip prompt)", false);
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
                    $Method = $Class->getMethod('handleBuildRequest');
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
    static function handleBuild(IBuildRequest $Request) {
        $Builder = new RouteBuilder($Request, new CPathBackendRoutes());
        $Builder->writeRoute('CLI /build', __CLASS__);
    }

    static function cls() {
        return __CLASS__;
    }
}