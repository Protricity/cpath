<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/27/13
 * Time: 3:54 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Misc;
use CPath\Base;
use CPath\Interfaces\IAPI;
use CPath\Interfaces\IResponse;
use CPath\Model\CLI;
use CPath\Model\Response;

class NotAnApiException extends \Exception {}
class APIFailedException extends \Exception {}

class ApiTester {
    private $mAPI;
    private $mRequest;
    public function __construct(IAPI $API, Array $request=NULL) {
        $this->mAPI = $API;
        $this->mRequest = $request;
    }

    public function test(Array $request=NULL) {
        $request = $this->mRequest + (Array) $request;
        $Response = $this->mAPI->execute($request);
        if(!($Response instanceof IResponse))
            $Response = new Response(true, "API executed successfully", $Response);
        if($Response->getStatusCode() != IResponse::STATUS_SUCCESS)
            throw new APIFailedException($Response->getMessage());
        return $Response;
    }

    public function testAndGet($path, Array $request=NULL) {
        $data = $this->test($request)->getData();
        return $data[$path];
    }

    static function fromCMD($_cmd) {
        $Cli = new CLI(is_array($_cmd) ? $_cmd : func_get_args());
        $Route = Base::findRoute($Cli->getRoute());
        $Api = $Route->getHandler();
        if(!($Api instanceof IAPI))
            throw new NotAnApiException(get_class($Api) . " does not implement IAPI");
        $Api->setRoute($Route); // TODO: Ugly
        return new static($Api, $Cli->getRequest());
    }

    /**
     * @param $_cmd
     * @return IResponse
     */
    static function cmd($_cmd) {
        return self::fromCMD(is_array($_cmd) ? $_cmd : func_get_args())->test();
    }
}