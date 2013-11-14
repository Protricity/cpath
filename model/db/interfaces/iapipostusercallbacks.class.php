<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Model\DB\Interfaces;

use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\PDOUserModel;

interface IAPIPostUserCallbacks {

    /**
     * Perform on successful API_Get execution
     * @param PDOUserModel $NewUser the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onPostUserExecute(PDOUserModel $NewUser, IRequest $Request, IResponse $Response);
}