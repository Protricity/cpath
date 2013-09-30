<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Interfaces;

use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;

interface IAPIExecute {

    /**
     * Perform on API creation
     * @param IRequest $Request
     * @return void
     */
    function onAPIPreExecute(IRequest $Request);


    /**
     * Perform after API execution
     * @param IRequest $Request
     * @param IResponse $Response
     * @return void
     */
    function onAPIPostExecute(IRequest $Request, IResponse $Response);
}