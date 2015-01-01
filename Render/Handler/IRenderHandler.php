<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/31/2014
 * Time: 4:33 PM
 */
namespace CPath\Render\Handler;

use CPath\Render\IRenderAll;
use CPath\Request\IRequest;

interface IRenderHandler
{
	/**
	 * Return true if the object can be rendered
	 * @param $Object
	 * @return bool
	 */
	function canHandle($Object);

	/**
	 * Render the object
	 * @param IRequest $Request
	 * @param $Object
	 * @return IRenderAll
	 */
	function getRenderer(IRequest $Request, $Object);
}