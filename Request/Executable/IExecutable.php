<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/15/14
 * Time: 9:11 PM
 */
namespace CPath\Request\Executable;

use CPath\Framework\Response\Interfaces\IResponse;

interface IExecutable
{
    /**
     * Execute a command and return a response. Does not render
     * @param IPrompt $Request the request to execute
     * @return IResponse the execution response
     */
    function execute(IPrompt $Request);
}