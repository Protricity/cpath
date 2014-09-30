<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 11:12 PM
 */
namespace CPath\Backend;

use CPath\Autoloader;
use CPath\Build\BuildRequestWrapper;
use CPath\Build\File;
use CPath\Build\IBuildRequest;
use CPath\Build\IBuildable;
use CPath\Build\MethodDocBlock;
use CPath\Response\IResponse;
use CPath\Response\Response;
use CPath\Request\CLI\CommandString;
use CPath\Request\Executable\IExecutable;
use CPath\Request\Executable\IPrompt;
use CPath\Request\IRequest;
use CPath\Request\IStaticRequestHandler;
use CPath\Request\Log\ILogListener;
use CPath\Request\Request;
use CPath\Response\ResponseRenderer;
use CPath\Route\RouteBuilder;

//if(!defined('\CPath\Autoloader'))
//    include_once(__DIR__ . "/../Autoloader.php");

class BuildRequestHandler implements IStaticRequestHandler, IBuildable, IExecutable
{
    const DOCTAG = 'build';
    private $mDefaults = false;

    /**
     * Execute a command and return a response. Does not render
     * @param \CPath\Request\IRequest $Request
     * @internal param \CPath\Request\Executable\IPrompt $Prompt the request prompt
     * @return \CPath\Response\IResponse the execution response
     */
    function execute(IRequest $Request) {
        $flags = 0;

        $OriginalRequest = $Request;
        $this->mDefaults = $Request->getValue('defaults', "Use Defaults? (Skip prompt)") || false;

        if (!$this->mDefaults && $Request->getValue('test', "Skip commit? (Test mode)"))
            $flags |= IBuildRequest::TEST_MODE;

        $flags |= IBuildRequest::IS_SESSION_BUILD;

        $BuildRequest = new BuildRequestWrapper($OriginalRequest, $flags);
        $this->buildAllFiles($BuildRequest);
        return new Response("Build complete");
    }


    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request instance for this build session
     * @return String|void always returns void
     */
    function buildAllFiles(IBuildRequest $Request) {
        $paths = Autoloader::getLoaderPaths();
        foreach($paths as $path)
            $Request->log("Path: " . $path);

        $Iterator = new File\Iterator\PHPFileIterator('/', $paths);

        $buildableClasses = array();
        while ($file = $Iterator->getNextFile()) {
            $Request->log("File: " . $file, ILogListener::VERBOSE);

            $Scanner = new File\PHPFileScanner($file);
            $results = $Scanner->scanClassTokens();
            foreach ($results[T_CLASS] as $fullClass => $tokens) {
                if(isset($tokens[T_IMPLEMENTS])) {
                    foreach ($tokens[T_IMPLEMENTS] as $implements) {
                        if (strpos($implements, 'IBuildable') !== false) {
                            $buildableClasses[] = $fullClass;
                        }
                    }
                }
            }
        }

        foreach ($buildableClasses as $class) {
            $Request->log("Found Class: " . $class, ILogListener::VERBOSE);

            $Class = new \ReflectionClass($class);
            if ($Class->implementsInterface('\CPath\Build\IBuildable')) {
                /** @var IBuildable $class */
                $Method = $Class->getMethod('handleStaticBuild');
                $MethodDoc = new MethodDocBlock($Method);
                if($Tag = $MethodDoc->getNextTag(self::DOCTAG)) {
                    $args = CommandString::parseArgs($Tag->getArgString());
                    if(isset($args['disable']) && $args['disable']) {
                        $Request->log("Class Building Disabled: " . $class);
                        continue;
                    }
                }

                try {
                    $Request->log("Building {$class}...");
                    $class::handleStaticBuild($Request);

                } catch (\Exception $ex) {
                    $Request->logEx($ex);
                    if($Request instanceof IPrompt)
                        $Request->prompt('error-resume', "Continue build?");

                    break;
                }

            } else {
                $Request->log("{$class} does not implement IBuildable");

            }
        }
    }

    // Static

    /**
     * Handle this request and render any content
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    static function handleStaticRequest(IRequest $Request) {
        $Inst = new BuildRequestHandler();
        $Response = $Inst->execute($Request);
        $Handler = new ResponseRenderer($Response);
        $Handler->render($Request);
    }

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request instance for this build session
     * @return String|void always returns void
     */
    static function handleStaticBuild(IBuildRequest $Request) {
        $Builder = new RouteBuilder($Request, new CPathBackendRoutes());
        $Builder->writeRoute('CLI /cpath/build', __CLASS__);
    }

    static function cls() {
        return __CLASS__;
    }
}

//$Build = new BuildRequestWrapper(Request::create());
//BuildRequestHandler::handleStaticBuild($Build);