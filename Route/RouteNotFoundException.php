<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/22/14
 * Time: 4:42 PM
 */
namespace CPath\Route;

use CPath\Request\Exceptions\RequestException;
use CPath\Response\IResponse;

class RouteNotFoundException extends RequestException
{
    const DEFAULT_HTTP_CODE = IResponse::HTTP_NOT_FOUND;
}