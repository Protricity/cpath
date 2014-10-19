<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 3:01 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Validation\IRequestValidation;

interface IRequestParameter extends IRequestValidation, IRenderHTML
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

