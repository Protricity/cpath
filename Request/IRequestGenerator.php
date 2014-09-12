<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 5:31 PM
 */
namespace CPath\Request;

interface IRequestGenerator
{
    /**
     * Generates and returns a new request
     * @return IRequest
     */
    function createRequest();
}