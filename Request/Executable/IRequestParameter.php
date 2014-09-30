<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/26/14
 * Time: 11:37 PM
 */
namespace CPath\Request\Executable;

interface IRequestParameter
{
    /**
     * Get the request parameter name
     * @return String
     */
    function getName();
}

