<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api\Interfaces;

use CPath\Interfaces\IRequest;

interface IValidation {
    /**
     * Validate and return request
     * @param IRequest $Request the pending request to validate
     * @throws ValidationException if a validation exception occurred
     */
    function validate(IRequest $Request);
}