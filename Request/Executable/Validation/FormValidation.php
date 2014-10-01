<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 4:16 PM
 */
namespace CPath\Request\Executable\Validation;

use CPath\Render\HTML\Element\HTMLForm;
use CPath\Request\Executable\IExecutable;
use CPath\Request\Parameter\Parameter;
use CPath\Request\Parameter\MappableParameterCallback;
use CPath\Request\IRequest;
use CPath\Request\RequestException;

class FormValidation
{
    private $mExec;

    public function __construct(IExecutable $Executable) {
        $this->mExec = $Executable;
    }

    /**
     * Validate this request or throw a ValidationException
     * @param IRequest $Request
     * @return mixed
     * @throws RequestException if the validation fails
     */
    function validateRequest(IRequest $Request) {
        $Executable = $this->mExec;
        $this->mExec->mapParameters(
            new MappableParameterCallback(
                function (Parameter $Parameter) use ($Request, $Executable) {
                    $Parameter->validateRequest($Request, $Executable);
                }
            )
        );
    }
}