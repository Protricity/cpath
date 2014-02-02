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
use CPath\Interfaces\IRequest;
use CPath\Request\CLI;
use CPath\Response\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\Response;

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
     * @return \CPath\Response\IResponse
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
        $Handler = $Route->loadHandler();

        if(!($Handler instanceof IAPI)) {
            throw new NotAnApiException(get_class($Handler) . " does not implement IAPI");
        }
        return new static($Handler, $Cli);
    }

    /**
     * @param $_cmd
     * @return \CPath\Response\IResponse
     */
    static function cmd($args, Array $request=NULL) {
        return self::fromCMD($args, $request)->test();
    }
}