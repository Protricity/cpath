<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 11:12 PM
 */
namespace CPath\Build\Handlers;

use CPath\Autoloader;
use CPath\Build\BuildRequestWrapper;
use CPath\Build\File;
use CPath\Build\IBuildable;
use CPath\Build\IBuildRequest;
use CPath\Build\MethodDocBlock;
use CPath\Request\CLI\CommandString;
use CPath\Request\Executable\IExecutable;
use CPath\Request\Executable\IPrompt;
use CPath\Request\IRequest;
use CPath\Response\IResponse;
use CPath\Response\Response;
use CPath\Response\ResponseRenderer;
use CPath\Route\CPathMap;
use CPath\Route\IRoutable;
use CPath\Route\RouteBuilder;

//if(!defined('\CPath\Autoloader'))
//    include_once(__DIR__ . "/../Autoloader.php");

class BuildRequestHandler implements IRoutable, IBuildable, IExecutable
{
    const DOCTAG = 'build';
    private $mDefaults = false;

	/**
	 * Execute a command and return a response. Does not render
	 * @param \CPath\Request\Form\IFormRequest|\CPath\Request\IRequest $Request
	 * @return IResponse the execution response
	 */
	function execute(IRequest $Request) {
        $flags = 0;

        $OriginalRequest = $Request;
        $this->mDefaults = $Request['defaults'] || false;

        if (!$this->mDefaults && $Request['test'])
            $flags |= IBuildRequest::TEST_MODE;

        $flags |= IBuildRequest::IS_SESSION_BUILD;

        $BuildRequest = new BuildRequestWrapper($OriginalRequest, $flags);
        $this->buildAllFiles($BuildRequest);
        return new Response("Build complete");
    }


    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request inst for this build session
     * @return void
     */
    function buildAllFiles(IBuildRequest $Request) {
        $paths = Autoloader::getLoaderPaths();
        foreach($paths as $path)
            $Request->log("Path: " . $path);

        $Iterator = new File\Iterator\PHPFileIterator('/', $paths);

        $buildableClasses = array();
        while ($file = $Iterator->getNextFile()) {
            $Request->log("File: " . $file, $Request::VERBOSE);

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
            $Request->log("Found Class: " . $class, $Request::VERBOSE);

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
                    $Request->log($ex, $Request::ERROR);
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
	 * Route the request to this class object and return the object
	 * @param IRequest $Request the IRequest inst for this render
	 * @param Object[]|null $Previous all previous response object that were passed from a handler, if any
	 * @param null|mixed $_arg [varargs] passed by route map
	 * @return void|bool|Object returns a response object
	 * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
	 * If false is returned, this static handler will be called again if another handler returns an object
	 * If an object is returned, it is passed along to the next handler
	 */
    static function routeRequestStatic(IRequest $Request, Array &$Previous = array(), $_arg=null) {
        $Inst = new BuildRequestHandler();
        $Response = $Inst->execute($Request);
        $Handler = new ResponseRenderer($Response);
        $Handler->render($Request);
    }

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request inst for this build session
     * @return void
     */
    static function handleStaticBuild(IBuildRequest $Request) {
        $Builder = new RouteBuilder($Request, new CPathMap());
        $Builder->writeRoute('CLI /cpath/build', __CLASS__);
    }

    static function cls() {
        return __CLASS__;
    }
}

//$Build = new BuildRequestWrapper(Request::create());
//BuildRequestHandler::handleStaticBuild($Build);