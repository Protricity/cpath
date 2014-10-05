<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 3:01 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Validation\IParameterValidation;

interface IRequestParameter extends IParameterValidation, IRenderHTML
{
    /**
     * Get parameter name
     * @return String
     */
    function getName();

	/**
	 * Get parameter description
	 * @return String
	 */
	function getDescription();
}

