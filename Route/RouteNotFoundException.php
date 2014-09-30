<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/22/14
 * Time: 4:42 PM
 */
namespace CPath\Route;

use CPath\Response\Exceptions\HTTPRequestException;
use CPath\Response\IResponseCode;

class RouteNotFoundException extends HTTPRequestException
{
    const DEFAULT_HTTP_CODE = IResponseCode::STATUS_NOT_FOUND;
}