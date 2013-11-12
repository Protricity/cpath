<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers\Api;

use CPath\Handlers\Api\Interfaces\IValidation;
use CPath\Handlers\Api\Interfaces\ValidationException;
use CPath\Interfaces\IRequest;

class Validation implements IValidation {
    protected $mCallback;
    /**
     * Create an APIValidation using a callback
     * @param Callable $callback which accepts the IRequest $Request for validation
     */
    function __construct($callback) {
        $this->mCallback = $callback;
    }

    /**
     * Validate and return request
     * @param IRequest $Request the pending request to validate
     * @throws ValidationException if a validation exception occurred
     */
    function validate(IRequest $Request) {
        $call = $this->mCallback;
        $call($Request);
    }
}