<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 7:17 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\Executable\IPrompt;
use CPath\Request\Executable\IPromptValidation;

class ValidateOptional
{
    private $mDefaultValue;
    private $mMessage;

    public function __construct($defaultValue, $description = null)
    {
        $this->mDefaultValue = $defaultValue;
        $this->mMessage = $description;
    }

    /**
     * Validate input
     * @param IPrompt $Prompt
     * @param String $paramName the parameter name
     * @return mixed returns the validated input
     * @throws ValidationException if the input fails to validate
     */
    function validate(IPrompt $Prompt, $paramName)
    {
        try {
            return $Prompt->prompt($paramName, $this->mDefaultValue);
        } catch (PromptException $ex) {
            return $this->mDefaultValue;
        }
    }
}