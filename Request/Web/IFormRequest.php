<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 11:36 PM
 */
namespace CPath\Request\Web;

use CPath\Request\Exceptions\FormFieldException;

interface IFormRequest
{
//
//    /**
//     * Get a form field value by name or return null
//     * @param string $name
//     * @return string|null the field value or null
//     */
//    function getFormFieldValue($name);

    /**
     * Prompt for a form field value by name
     * @param string $name the form field name
     * @param string|null $description optional description for this form field
     * @param string|null $defaultValue optional default value if prompt fails
     * @return string the parameter value
     * @throws \CPath\Request\Exceptions\FormFieldException if a prompt failed to produce a result
     */
    function promptForm($name, $description=null, $defaultValue=null);
}