<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/2/2015
 * Time: 9:28 AM
 */
namespace CPath\Request\Executable;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;
use CPath\Request\Session\ISessionRequest;
use CPath\Response\IResponse;

abstract class AbstractSessionExecutable implements IExecutable
{
	abstract function executeInSession(ISessionRequest $Request);

	/**
	 * Execute a command and return a response. Does not render
	 * @param IRequest $Request
	 * @throws RequestException
	 * @return IResponse the execution response
	 */
	function execute(IRequest $Request) {
		if (!$Request instanceof ISessionRequest)
			throw new RequestException("Session Request is required");

		return $this->executeInSession($Request);
	}
}