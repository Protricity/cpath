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
use CPath\Response\ResponseRenderer;
use CPath\Response\IResponseCode;
use CPath\Route\RouteBuilder;
use CPath\UnitTest\ITestable;
use CPath\UnitTest\UnitTestRequestWrapper;

//if(!defined('\CPath\Autoloader'))
//    include_once(__DIR__ . "/../Autoloader.php");

class TestRequestHandler implements IStaticRequestHandler, IBuildable, IExecutable
{
    const DOCTAG = 'test';
    private $mDefaults = false;
    private $mResponse;
    private $mExceptions = array();

    /**
     * Execute a command and return a response. Does not render
     * @param \CPath\Request\IRequest $Request
     * @internal param \CPath\Request\Executable\IPrompt $Prompt the request prompt
     * @return IResponse the execution response
     */
    function execute(IRequest $Request) {
        $flags = 0;

        $OriginalRequest = $Request;
        $this->mDefaults = $Request->getValue('defaults', "Use Defaults? (Skip prompt)") || false;

        if (!$this->mDefaults && $Request->getValue('test', "Skip commit? (Test mode)"))
            $flags |= IBuildRequest::TEST_MODE;

        $flags |= IBuildRequest::IS_SESSION_BUILD;

        $BuildRequest = new BuildRequestWrapper($OriginalRequest, $flags);
        $this->mResponse = new Response("Test complete");
        $this->mExceptions = array();
        $this->testAllFlies($BuildRequest);
        return $this->mResponse;
    }


    /**
     * Handle this request and render any content
     * @param IBuildRequest $Request the test request instance for this test session
     * @return String|void always returns void
     */
    function testAllFlies(IBuildRequest $Request) {
        $paths = Autoloader::getLoaderPaths();
        foreach($paths as $path)
            $Request->log("Path: " . $path);

        $Iterator = new File\Iterator\PHPFileIterator('/', $paths);

        $testableClasses = array();
        while ($file = $Iterator->getNextFile()) {
            $Request->log("File: " . $file, ILogListener::VERBOSE);

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

        foreach ($testableClasses as $class) {
            $Request->log("Found Class: " . $class, ILogListener::VERBOSE);

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
                    $Request->log("*** Test passed ***\n");


                } catch (\Exception $ex) {
                    $Request->logEx($ex);
                    if($Request instanceof IPrompt)
                        $Request->prompt('error-resume', "Continue test?");

                    $this->mExceptions[] = $ex;
                    $this->mResponse = new Response(count($this->mExceptions) . " Exception(s) occurred", false);
                    break;
                }

            } else {
                $Request->log("{$class} does not implement ITestable");

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
        $Inst = new TestRequestHandler();
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
        $Builder->writeRoute('CLI /cpath/test', __CLASS__);
    }

    static function cls() {
        return __CLASS__;
    }
}

//$Build = new BuildRequestWrapper(Request::create());
//BuildRequestHandler::handleStaticBuild($Build);