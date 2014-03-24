<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/22/14
 * Time: 4:42 PM
 */
namespace CPath\Framework\Route\Exceptions;

use CPath\Framework\Response\Exceptions\CodedException;
use CPath\Framework\Response\Interfaces\IResponseCode;

class RouteNotFoundException extends CodedException
{
    const DEFAULT_CODE = IResponseCode::STATUS_NOT_FOUND;
}