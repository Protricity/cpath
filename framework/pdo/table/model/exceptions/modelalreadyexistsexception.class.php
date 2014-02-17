<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/16/14
 * Time: 7:59 PM
 */
namespace CPath\Framework\PDO\Table\Model\Exceptions;

use CPath\Framework\Response\Exceptions\CodedException;
use CPath\Framework\Response\Interfaces\IResponse;

class ModelAlreadyExistsException extends CodedException {
    const DEFAULT_CODE = IResponse::STATUS_CONFLICT;
}