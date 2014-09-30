<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;

interface IResponseCode {
    const STATUS_SUCCESS = 200;

    const STATUS_SEE_OTHER = 303;
    const STATUS_TEMPORARY_REDIRECT = 307;

    const STATUS_ERROR = 400;
    const STATUS_NOT_FOUND = 404;
    const STATUS_CONFLICT = 409;

    const STR_CODE = 'code';


    /**
     * Get the request status code
     * @return int
     */
    function getCode();
}