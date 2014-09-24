<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

interface IAPIGetCallbacks {

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return \CPath\Framework\API\Field\\CPath\Framework\API\Field\Interfaces\IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     */
    function prepareGetFields(Array &$fields);

    /**
     * Perform on successful GetAPI execution
     * @param PDOPrimaryKeyModel $Model the returned model
     * @param \CPath\Request\IRequest $Request
     * @param \CPath\Response\IResponse $Response
     * @return \CPath\Response\IResponse|null
     */
    function onGetExecute(PDOPrimaryKeyModel $Model, IRequest $Request, IResponse $Response);
}