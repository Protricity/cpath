<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Response\Interfaces;

interface IResponseCode {
    const STATUS_SUCCESS = 200;
    const STATUS_ERROR = 400;
    const STATUS_NOT_FOUND = 404;
    const STATUS_CONFLICT = 409;

    /**
     * Get the Response status code
     * @return int
     */
    function getStatusCode();
}