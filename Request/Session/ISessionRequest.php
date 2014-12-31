<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/25/14
 * Time: 1:27 PM
 */
namespace CPath\Request\Session;

use CPath\Request\Exceptions\RequestException;
use CPath\Request\IRequest;

interface ISessionRequest extends IRequest
{
    /**
     * Return a referenced array representing the request session
     * @param String|null [optional] $key if set, retrieves &$[Session][$key] instead of &$[Session]
     * @return array|mixed|null
     * @throws SessionRequestException if no session was started yet
     */
    function &getSession($key = null);

	/**
	 * Start a new session
	 * @internal param bool $reset if true, session will be reset
	 * @return bool true if session was started, otherwise false
	 */
	function startSession();

	/**
	 * End current session
	 * @return bool true if session was started, otherwise false
	 * @throws SessionRequestException if session wasn't active
	 */
	function endSession();

	/**
	 * Returns true if the session is active, false if inactive
	 * @return bool
	 */
	function hasActiveSession();
}
