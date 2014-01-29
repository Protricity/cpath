<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IValidateRequest {
    /**
     * Validate and return an IRequest
     * @param IRequest $Request the request to validate and modify
     * @return void
     */
    function validateRequest(IRequest $Request);
}