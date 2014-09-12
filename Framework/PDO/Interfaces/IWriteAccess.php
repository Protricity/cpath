<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

use CPath\Framework\PDO\InvalidPermissionException;
use CPath\Framework\PDO\Table\Model\Types\PDOModel;
use CPath\Request\IRequest;


interface IWriteAccess {

    const INTENT_PATCH = 4;
    const INTENT_DELETE = 5;

    /**
     * Assert permission in default API calls PATCH, and DELETE
     * @param PDOModel $Model the Model to assert access upon
     * @param IRequest $Request
     * @param int $intent the read intent.
     * Typically IWriteAccess::INTENT_PATCH or IWriteAccess::INTENT_DELETE.
     * @throws InvalidPermissionException if the user does not have permission to handle this Model
     */
    function assertWriteAccess(PDOModel $Model, IRequest $Request, $intent);
}