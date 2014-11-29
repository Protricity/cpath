<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/3/14
 * Time: 3:01 PM
 */
namespace CPath\Request\Parameter;

use CPath\Data\Describable\IDescribable;
use CPath\Render\HTML\Element\Form\IHTMLFormField;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

interface IRequestParameter extends IHTMLFormField, IDescribable, IRenderHTML
{
	/**
	 * Get parameter description
	 * @return String
	 */
	//function getDescription();

	/**
	 * Get the request value
	 * @param IRequest $Request
	 * @throws RequestException if the parameter failed validated
	 * @return mixed
	 */
	//function getInputValue(IRequest $Request);

	/**
	 * Validate the request and return the validated content
	 * @param IRequest $Request
	 * @return mixed validated content
	 */
	//function validateRequest(IRequest $Request);
}

