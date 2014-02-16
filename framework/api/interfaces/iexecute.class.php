<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Interfaces;

use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;

interface IExecute {

    /**
     * Perform on API creation
     * @param IRequest $Request
     * @return void
     */
    function onAPIPreExecute(IRequest $Request);


    /**
     * Perform after successful API execution
     * Note: is not performed when exceptions are thrown or if the response status is not 200 (success)
     * @param IRequest $Request
     * @param IResponse $DataResponse
     * @return void
     */
    function onAPIPostExecute(IRequest $Request, IResponse $Response);
}