<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/25/14
 * Time: 1:27 PM
 */
namespace CPath\Request\Session;

use CPath\Request\IRequest;

interface ISessionRequest extends IRequest
{
    /**
     * Return a referenced array representing the request session
     * @param String|null [optional] $key if set, retrieves &$[Session][$key] instead of &$[Session]
     * @return array|mixed|null
     */
    function &getSession($key = null);

	/**
	 * Start a new session
	 * @param bool $reset if true, session will be reset
	 * @return bool true if session was started, otherwise false
	 */
	function startSession($reset=false);

	/**
	 * End current session
	 * @return bool true if session was started, otherwise false
	 */
	function endSession();

}
