<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Misc;
use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Request\Types\CLIRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Framework\Response\Types\ExceptionResponse;
use CPath\Routes;

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
            $Response = new DataResponse(true, "API executed successfully", $Response);
        if($Response instanceof ExceptionResponse)
            throw $Response->getException();
        if($Response->getStatusCode() != IResponse::STATUS_SUCCESS)
            throw new APIFailedException($Response->getMessage());
        return $Response;
    }

    static function fromCMD($args, Array $request=NULL) {
        $Cli = CLIRequest::fromArgs($args, $request);
        $Routes = new Routes;
        $Handler = $Routes->getHandlerFromRequest($Cli);

        if(!($Handler instanceof IAPI)) {
            throw new NotAnApiException(get_class($Handler) . " does not implement IAPI");
        }
        return new static($Handler, $Cli);
    }

    /**
     * @param $args
     * @param array $request
     * @internal param $_cmd
     * @return IResponse
     */
    static function cmd($args, Array $request=NULL) {
        return self::fromCMD($args, $request)->test();
    }
}