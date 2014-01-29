<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\PDO\Templates\User\PDOUserModel;
use CPath\Handlers\Api\Interfaces\IField;
use CPath\Handlers\Api\Interfaces\InvalidAPIException;
use CPath\Interfaces\IRequest;
use CPath\Response\IResponse;

interface IAPIPostUserCallbacks {

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     * @throws InvalidAPIException
     */
    function preparePostUserFields(Array &$fields);

    /**
     * Perform on successful API_Get execution
     * @param PDOUserModel $NewUser the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onPostUserExecute(PDOUserModel $NewUser, IRequest $Request, IResponse $Response);
}