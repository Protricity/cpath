<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Misc;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Interfaces\IHandlerSet;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\ExceptionResponse;
use CPath\Request\CLI;
use CPath\Model\Response;

class NotAnApiException extends \Exception {}
class APIFailedException extends \Exception {}

class ApiTester {
    /** @var IAPI */
    private $mAPI;
    private $mRequest;
    public function __construct(IAPI $API, IRequest $Request) {
        $this->mAPI = $API;
        $this->mRequest = $Request;
    }

    /**
     * @param array $request
     * @return IResponse
     * @throws APIFailedException
     * @throws \Exception
     */
    public function test(Array $request=NULL) {
        if($request)
            $this->mRequest->merge($request);
        $Response = $this->mAPI->execute($this->mRequest);
        if(!($Response instanceof IResponse))
            $Response = new Response(true, "API executed successfully", $Response);
        if($Response instanceof ExceptionResponse)
            throw $Response->getException();
        if($Response->getStatusCode() != IResponse::STATUS_SUCCESS)
            throw new APIFailedException($Response->getMessage());
        return $Response;
    }

    static function fromCMD($args, Array $request=NULL) {
        $Cli = CLI::fromArgs($args, $request);
        $Route = $Cli->findRoute();
        $Api = $Route->getHandler();
        if($Api instanceof IHandlerSet)
            $Api = $Api->get($Cli->getNextArg());
        if(!($Api instanceof IAPI))
            throw new NotAnApiException(get_class($Api) . " does not implement IAPI");
        return new static($Api, $Cli);
    }

    /**
     * @param $_cmd
     * @return IResponse
     */
    static function cmd($args, Array $request=NULL) {
        return self::fromCMD($args, $request)->test();
    }
}