<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 11:12 PM
 */
namespace CPath\UnitTest\Handlers;

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
use CPath\Route\CPathMap;
use CPath\Route\IRoutable;
use CPath\Route\RouteBuilder;
use CPath\UnitTest\ITestable;
use CPath\UnitTest\UnitTestRequestWrapper;

//if(!defined('\CPath\Autoloader'))
//    include_once(__DIR__ . "/../Autoloader.php");

class TestRequestHandler implements IRoutable, IBuildable, IExecutable
{
    const DOCTAG = 'test';
    private $mDefaults = false;
    private $mResponse;

	/**
	 * Execute a command and return a response. Does not render
	 * @param IRequest $Request
	 * @internal param \CPath\Request\Executable\IPrompt $Prompt the request prompt
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
        $this->mResponse = null;
        $this->testAllFlies($BuildRequest);
        return $this->mResponse ?: $this->mResponse = new Response("Test complete");
    }

	const CACHE_FILE = '.cache';
	const CACHE_EXPIRE = 3600;

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the test request inst for this test session
     * @return void
     */
    function testAllFlies(IBuildRequest $Request) {
        $paths = Autoloader::getLoaderPaths();
        foreach($paths as $path)
            $Request->log("Path: " . $path);

		$cachePath = __DIR__ . '/' . self::CACHE_FILE;
	    $testableClasses = array();
	    if(file_exists($cachePath) && filemtime($cachePath) + self::CACHE_EXPIRE > time()) {
		    $testableClasses = json_decode(file_get_contents($cachePath), true);
		    $Request->log("Using cached class list", $Request::VERBOSE);
	    }

	    if(!$testableClasses) {
		    $testableClasses = array();
		    $Iterator = new File\Iterator\PHPFileIterator('/', $paths);
	        while ($file = $Iterator->getNextFile()) {
	            $Request->log("File: " . $file, $Request::VERBOSE);

	            $Scanner = new File\PHPFileScanner($file);
	            $results = $Scanner->scanClassTokens();
	            foreach ($results[T_CLASS] as $fullClass => $tokens) {
	                if(isset($tokens[T_IMPLEMENTS])) {
	                    foreach ($tokens[T_IMPLEMENTS] as $implements) {
	                        if (strpos($implements, 'ITestable') !== false) {
	                            $testableClasses[] = $fullClass;
	                        }
	                    }
	                }
	            }
	        }

			$var = json_encode($testableClasses);
		    file_put_contents($cachePath, $var);
	    }

	    /** @var \Exception[] $Exs */
	    $Exs = array();
        foreach ($testableClasses as $class) {
            $Request->log("Found Class: " . $class, $Request::VERBOSE);

            $Class = new \ReflectionClass($class);
            if ($Class->implementsInterface('\CPath\UnitTest\ITestable')) {
                /** @var ITestable $class */
                $Method = $Class->getMethod('handleStaticUnitTest');
                $MethodDoc = new MethodDocBlock($Method);
                if($Tag = $MethodDoc->getNextTag(self::DOCTAG)) {
                    $args = CommandString::parseArgs($Tag->getArgString());
                    if(isset($args['disable']) && $args['disable']) {
                        $Request->log("Class Testing Disabled: " . $class);
                        continue;
                    }
                }

                try {
                    $Request->log("*** Testing {$class} ***");
                    $UnitTestRequest = new UnitTestRequestWrapper($Request);
                    $class::handleStaticUnitTest($UnitTestRequest);
                    $Request->log(sprintf("*** Test passed (%d) ***\n", $UnitTestRequest->getAssertionCount()));


                } catch (\Exception $ex) {
                    $Request->log($ex, $Request::ERROR);
                    if($Request instanceof IPrompt)
                        $Request->prompt('error-resume', "Continue test?");

	                $Exs[] = $ex;
                    break;
                }

            } else {
                $Request->log("{$class} does not implement ITestable");

            }
        }

	    if($Exs) {
		    $message   = sizeof($Exs) . " Exception(s) occurred during validation:";
		    foreach($Exs as $Ex)
			    $message .= "\n\t" . $Ex->getMessage();
		    $this->mResponse = new Response($message, IResponse::HTTP_ERROR, $Exs[0]);
	    }
    }

    // Static

	/**
	 * Route the request to this class object and return the object
	 * @param IRequest $Request the IRequest inst for this render
	 * @param Object[]|null $Previous all previous response object that were passed from a handler, if any
     * @param Object[]|null $Previous all previous response object that were passed from a handler, if any
	 * @param null|mixed $_arg [varargs] passed by route map
	 * @return void|bool|Object returns a response object
	 * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
	 * If false is returned, this static handler will be called again if another handler returns an object
	 * If an object is returned, it is passed along to the next handler
	 */
	static function routeRequestStatic(IRequest $Request, Array $Previous=array(), $_arg=null) {
        $Inst = new TestRequestHandler();
        return $Inst->execute($Request);
//        $Handler = new ResponseRenderer($Response);
//        $Handler->render($Request);
    }

    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the build request inst for this build session
     * @return void
     */
    static function handleStaticBuild(IBuildRequest $Request) {
        $Builder = new RouteBuilder($Request, new CPathMap());
        $Builder->writeRoute('CLI /cpath/test', __CLASS__);
	    @unlink(__DIR__ . '/' . self::CACHE_FILE);
    }

//    static function cls() {
//        return __CLASS__;
//    }
}

//$Build = new BuildRequestWrapper(Request::create());
//BuildRequestHandler::handleStaticBuild($Build);