<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/1/2015
 * Time: 10:57 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

class URLValidation implements IValidation
{
    private $required;

    public function __construct($required=false) {
        $this->required = $required;
    }

    /**
     * Validate the request value and return the validated value
     * @param IRequest $Request
     * @param $value
     * @param null $fieldName
     * @throws \CPath\Request\Exceptions\RequestException
     * @return mixed validated value
     */
    function validate(IRequest $Request, $value = null, $fieldName = null) {
        if((!$value === null || $value === '') && !$this->required)
            return $value;

        $value = filter_var($value, FILTER_VALIDATE_URL);
        if (!$value)
            throw new RequestException("Invalid url format");

        return $value;
    }
}