<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 6/14/14
 * Time: 10:33 AM
 */
namespace CPath\Framework\Request\Common;

use CPath\Framework\Request\Interfaces\IRequest;

class ExceptionRequestWrapper extends RequestWrapper
{
    private $mException;

    public function __construct(IRequest $Request, \Exception $Exception)
    {
        parent::__construct($Request);
        $this->mException = $Exception;
    }

    public function getException()
    {
        return $this->mException;
    }
}