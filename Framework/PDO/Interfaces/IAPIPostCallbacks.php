<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

interface IAPIPostCallbacks {

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return \CPath\Framework\API\Field\\CPath\Framework\API\Field\Interfaces\IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     */
    function preparePostFields(Array &$fields);

    /**
     * Perform on successful PostAPI execution.
     * Note: Leave empty if unused.
     * @param PDOModel $NewModel the returned model
     * @param IRequest $Request
     * @param \CPath\Response\IResponse $DataResponse
     * @return \CPath\Response\IResponse|null
     */
    function onPostExecute(PDOModel $NewModel, IRequest $Request, \CPath\Response\IResponse $Response);

    /**
     * Modify the PostAPI IRequest and/or return a row of fields to use in PDOModel::createFromArray
     * Note: Leave empty if unused.
     * @param Array &$row an associative array of key/value pairs
     * @param IRequest $Request
     * @return Array|null a row of key/value pairs to insert into the database
     */
    function preparePostInsert(Array &$row, IRequest $Request);
}