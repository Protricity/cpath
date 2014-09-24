<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/22/14
 * Time: 4:42 PM
 */
namespace CPath\Route;

use CPath\Response\Exceptions\CodedException;
use CPath\Response\IResponseCode;

class RouteNotFoundException extends CodedException
{
    const DEFAULT_CODE = IResponseCode::STATUS_NOT_FOUND;
}