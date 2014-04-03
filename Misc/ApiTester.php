<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Misc;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Framework\Data\Wrapper\IWrapper;
use CPath\Framework\Request\Common\CLIRequest;
use CPath\Framework\Request\Interfaces\IRequest;
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
     * @throws \Exception
     * @throws APIFailedException
     * @return IResponse
     */
    public function test(Array $request=NULL) {
        if($request)
            $this->mRequest->merge($request);
        $Response = $this->mAPI->execute($this->mRequest);
        if(!($Response instanceof IResponse))
            $Response = new DataResponse(true, "API executed successfully", $Response);
        if($Response instanceof ExceptionResponse)
            throw $Response->getException();
        if($Response->getCode() != IResponse::STATUS_SUCCESS)
            throw new APIFailedException($Response->getMessage());
        return $Response;
    }

    static function fromCMD($args, Array $request=NULL) {
        $Cli = CLIRequest::fromArgs($args, $request);
        $Routes = new Routes;
        $Renderer = $Routes->routeRequest($Cli, $Routes);

//        if($Renderer instanceof IRenderAggregate)
//            $Renderer = $Renderer->getRenderer($Cli);

        if($Renderer instanceof IWrapper)
            $Renderer = $Renderer->getWrappedObject();

        if(!$Renderer instanceof IAPI)
            throw new NotAnApiException(get_class($Renderer) . " does not implement IAPI");

        return new static($Renderer, $Cli);
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