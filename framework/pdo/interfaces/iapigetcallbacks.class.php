<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\Api\Interfaces\IField;
use CPath\Framework\PDO\Model\PDOPrimaryKeyModel;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Response\IResponse;

interface IAPIGetCallbacks {

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     */
    function prepareGetFields(Array &$fields);

    /**
     * Perform on successful API_Get execution
     * @param PDOPrimaryKeyModel $Model the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onGetExecute(PDOPrimaryKeyModel $Model, IRequest $Request, IResponse $Response);
}