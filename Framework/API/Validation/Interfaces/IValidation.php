<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Validation\Interfaces;

use CPath\Request\IRequest;

interface IValidation {
    /**
     * Validate and return request
     * @param IRequest $Request the pending request to validate
     * @throws \CPath\Framework\API\Exceptions\ValidationException if a validation exception occurred
     */
    function validate(IRequest $Request);
}