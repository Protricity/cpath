<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Validation;

use CPath\Framework\API\Validation\Interfaces\IValidation;
use CPath\Request\IRequest;

class CallbackValidation implements IValidation {
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
     * @param \CPath\Request\IRequest $Request the pending request to validate
     * @throws \CPath\Framework\API\Exceptions\ValidationException if a validation exception occurred
     */
    function validate(IRequest $Request) {
        $call = $this->mCallback;
        $call($Request);
    }
}