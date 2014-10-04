<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 3:01 PM
 */
namespace CPath\Request\Parameter;

use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

interface IRequestParameter extends IRenderHTML
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

	/**
	 * Validate and return the parameter value
	 * @param IRequest $Request
	 * @param $value
	 * @return mixed request value
	 */
	function validate(IRequest $Request, $value);
}