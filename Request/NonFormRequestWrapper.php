<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/3/2015
 * Time: 12:58 PM
 */
namespace CPath\Request;

use CPath\Request\Form\IFormRequest;

class NonFormRequestWrapper extends AbstractRequestWrapper // TODO: implements ISessionRequest
{
	function __construct(IFormRequest $Request) {
		parent::__construct($Request);
	}
}