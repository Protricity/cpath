<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/28/14
 * Time: 5:39 PM
 */
namespace CPath\Request\Validation;

interface IRequestParameter
{
    /**
     * Get Parameter Name
     * @return mixed
     */
    function getName();
    /**
     * Get Parameter Description
     * @return mixed
     */
    function getDescription();

    /**
     * Get Parameter Name
     * @return mixed
     */
    function getValue();

    /**
     * Returns true if the parameter is required, otherwise false
     * @return bool
     */
    function isRequired();
}