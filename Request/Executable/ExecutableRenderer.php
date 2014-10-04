<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 8:33 PM
 */
namespace CPath\Request\Executable;

use CPath\Render\HTML\Attribute;
use CPath\Request\IFormRequest;
use CPath\Request\IRequest;
use CPath\Response\Common\ExceptionResponse;
use CPath\Response\IResponse;
use CPath\Response\Response;
use CPath\Response\ResponseRenderer;
use CPath\Route\IRoute;


class ExecutableHandler implements IRoute {

    public function __construct() {
    }

    /**
     * Renders a response object or returns false
     * @param IRequest $Request the IRequest instance for this render
     * @internal param \CPath\Request\Executable\IExecutable $Executable
     * @return bool returns false if no rendering occurred
     */
    function render(IRequest $Request)
    {
        if(!$Executable instanceof IExecutable)
            return false;

        try {
//            if($Executable instanceof IParameterMap)
//                $Request->setRequestParameters($Executable);
            $Response = $Executable->execute($Request)
                ?: new Response("No response returned by " . get_class($Executable) . '::execute', false);

        } catch (IResponse $ex) {
            $Response = $ex;

        } catch (\Exception $ex) {
            $Response = new ExceptionResponse($ex);

        }

        $Request->log($Response);

        $Renderer = new ResponseRenderer();
        $Renderer->render($Request);

        return true;
    }

    /**
     * Route the request to this class object and return the object
     * @param IRequest $Request the IRequest instance for this render
     * @param Object|null $Previous a previous response object that was passed to this handler or null
     * @param null|mixed $_arg [varargs] passed by route map
     * @return void|bool|Object returns a response object
     * If nothing is returned (or bool[true]), it is assumed that rendering has occurred and the request ends
     * If false is returned, this static handler will be called again if another handler returns an object
     * If an object is returned, it is passed along to the next handler
     */
    static function routeRequestStatic(IRequest $Request, $Executable = null, $_arg = null)
    {
        if(!$Executable instanceof IExecutable)
            return false;

        if($Request instanceof IFormRequest) {
            try {
//            if($Executable instanceof IParameterMap)
//                $Request->setRequestParameters($Executable);
                $Response = $Executable->execute($Request)
                    ?: new Response("No response returned by " . get_class($Executable) . '::execute', false);

            } catch (IResponse $ex) {
                $Response = $ex;

            } catch (\Exception $ex) {
                $Response = new ExceptionResponse($ex);

            }
            $Request->log($Response);
            return $Response;
        } else {

        }



        $Renderer = new ResponseRenderer();
        $Renderer->render($Request);

        return true;
    }
}