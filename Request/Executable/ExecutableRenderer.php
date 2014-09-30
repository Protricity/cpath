<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 8:33 PM
 */
namespace CPath\Request\Executable;

use CPath\Render\HTML\Attribute;
use CPath\Request\IRequest;
use CPath\Request\Log\StaticLogger;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\Response;
use CPath\Response\IResponse;
use CPath\Response\ResponseRenderer;

class ExecutableRenderer extends ResponseRenderer {

    private $mExecutable;
    private $mLastResponse = null;

    public function __construct(IExecutable $Executable) {
        $this->mExecutable = $Executable;
    }

    protected function getResponse(IRequest $Request) {
        if($this->mLastResponse)
            return $this->mLastResponse;
        try {
            $Response = $this->mExecutable->execute($Request)
                ?: new Response("No response", false);
        } catch (IResponse $ex) {
            $Response = $ex;
        } catch (\Exception $ex) {
            $Response = new ExceptionResponse($ex);
        }
        $this->mLastResponse = $Response;

        $Request->log($Response->getMessage());

        return $Response;
    }
}