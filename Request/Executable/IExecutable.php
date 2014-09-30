<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/15/14
 * Time: 9:11 PM
 */
namespace CPath\Request\Executable;

use CPath\Response\IResponse;
use CPath\Request\IRequest;

interface IExecutable
{
    /**
     * Execute a command and return a response. Does not render
     * @param IRequest $Request
     * @return IResponse the execution response
     */
    function execute(IRequest $Request);
}
