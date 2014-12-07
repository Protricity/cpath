<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/15/14
 * Time: 9:11 PM
 */
namespace CPath\Request\Executable;

use CPath\Render\HTML\Element\Form\HTMLForm;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

interface IExecutable
{
	/**
	 * Execute a command and return a response. Does not render
	 * @param IRequest $Request
	 * @return IResponse the execution response
	 */
    function execute(IRequest $Request);
}

interface IFormExecutable extends IExecutable {

	/**
	 * Return an HTMLForm instance for this executable
	 * @return HTMLForm
	 */
	function getForm();
}