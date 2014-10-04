<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/16/14
 * Time: 7:59 PM
 */
namespace CPath\Framework\PDO\Table\Model\Exceptions;

use CPath\Response\Exceptions\HTTPRequestException;
use CPath\Response\IResponse;

class ModelAlreadyExistsException extends HTTPRequestException {
    const DEFAULT_HTTP_CODE = IResponse::HTTP_CONFLICT;
}

