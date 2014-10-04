<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 3:02 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\IFormRequest;
use CPath\Request\RequestException;
use CPath\Request\RequestWrapper;

class FormRequestWrapper extends RequestWrapper implements IFormRequest
{

    /**
     * Get a request value by parameter name or null if not found
     * @param string $fieldName the parameter name
     * @param string $description [optional] description for this prompt
     * @param int $flags use ::PARAM_REQUIRED for required fields
     * @return mixed the parameter value
     * @throws RequestException if the value was not found
     */
    function getFormValue($fieldName, $description = null, $flags = 0)
    {
        return $this->getValue($fieldName, $description);
    }
}