<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Validation\Interfaces;

use CPath\Framework\Api\Exceptions\ValidationException;
use CPath\Framework\Request\Interfaces\IRequest;

interface IValidation {
    /**
     * Validate and return request
     * @param IRequest $Request the pending request to validate
     * @throws \CPath\Framework\Api\Exceptions\ValidationException if a validation exception occurred
     */
    function validate(IRequest $Request);
}