<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/20/14
 * Time: 6:54 PM
 */
namespace CPath\Request\Validation;

use CPath\Request\Validation\ValidationException;
use CPath\Request\Executable\IPrompt;
use CPath\Request\Executable\IPromptValidation;

class ValidationUtil
{
    private $mPrompt;

    public function __construct(IPrompt $Prompt) {
        $this->mPrompt = $Prompt;
    }
}

