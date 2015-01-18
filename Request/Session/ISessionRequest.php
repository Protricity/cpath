<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/25/14
 * Time: 1:27 PM
 */
namespace CPath\Request\Session;

interface ISessionRequest
{
	/**
	 * Return a referenced array representing the request session
	 * @return array
	 * @throws SessionRequestException if no session was started yet
	 */
	function &getSession();

	/**
	 * Start a new session
	 * @internal param bool $reset if true, session will be reset
	 * @return bool true if session was started, otherwise false
	 */
	function startSession();

	/**
	 * End session
	 * @return bool true if session was ended, otherwise false
	 * @throws SessionRequestException if session wasn't active
	 */
	function endSession();

	/**
	 * Destroy session data
	 * @return bool true if session was destroyed, otherwise false
	 * @throws SessionRequestException if session wasn't active
	 */
	function destroySession();

	/**
	 * Returns true if the session is active, false if inactive
	 * @return bool
	 */
	function hasSessionCookie();

	/**
	 * Returns true if the session has started
	 * @return bool
	 */
	function isStarted();
}
