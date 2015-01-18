<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/16/2015
 * Time: 9:43 AM
 */
namespace CPath\Request\Session;

class SocketSessionRequest implements ISessionRequest {

	/**
	 * Return a referenced array representing the request session
	 * @return array
	 * @throws SessionRequestException if no session was started yet
	 */
	function &getSession() {
		// TODO: Implement getSession() method.
	}

	/**
	 * Start a new session
	 * @internal param bool $reset if true, session will be reset
	 * @return bool true if session was started, otherwise false
	 */
	function startSession() {
		// TODO: Implement startSession() method.
	}

	/**
	 * End session
	 * @return bool true if session was ended, otherwise false
	 * @throws SessionRequestException if session wasn't active
	 */
	function endSession() {
		// TODO: Implement endSession() method.
	}

	/**
	 * Destroy session data
	 * @return bool true if session was destroyed, otherwise false
	 * @throws SessionRequestException if session wasn't active
	 */
	function destroySession() {
		// TODO: Implement destroySession() method.
	}

	/**
	 * Returns true if the session is active, false if inactive
	 * @return bool
	 */
	function hasSessionCookie() {
		// TODO: Implement hasSessionCookie() method.
	}

	/**
	 * Returns true if the session has started
	 * @return bool
	 */
	function isStarted() {
		// TODO: Implement isStarted() method.
	}
}

class SessionRequest implements ISessionRequest
{
	public function __construct() {
	}

//	public function __destruct(){
//		if($this->mSessionID && session_id() === $this->mSessionID) {
//			session_write_close();
//		}
//	}

	/**
	 * Return a referenced array representing the request session
	 * @return array
	 * @throws SessionRequestException if no session was started yet
	 */
	function &getSession() {
		if(!isset($_SESSION))
			throw new SessionRequestException("No active session");
		return $_SESSION;
	}

	/**
	 * Start a new session
	 * @internal param bool $reset if true, session will be reset
	 * @return bool true if session was started, otherwise false
	 */
	function startSession() {
		if(isset($_SESSION))
			return true;
		//throw new SessionRequestException("Session already active");
//
//		if ($this->mHandler) {
//			session_set_save_handler($this->mHandler, true);
//		}

		if(!session_start())
			throw new SessionRequestException("Session did not start");

		if(!session_start())
			throw new SessionRequestException("Session did not start");

		return true;
	}

	/**
	 * End current session
	 * @return bool true if session was started, otherwise false
	 * @throws SessionRequestException if session wasn't active
	 */
	function endSession() {
		if(!isset($_SESSION))
			throw new SessionRequestException("No active session");

		session_write_close();
		return true;
	}

	/**
	 * End current session
	 * @return bool true if session was destroyed, otherwise false
	 * @throws SessionRequestException if session wasn't active
	 */
	function destroySession() {
		if(!isset($_SESSION))
			throw new SessionRequestException("No active session");

		//remove PHPSESSID from browser
		if ($this->hasSessionCookie())
			setcookie( session_name(), "", time()-3600, "/" );

		//clear session from globals
		$_SESSION = array();

		//clear session from disk
		return session_destroy();
	}

	/**
	 * Returns true if the session is active, false if inactive
	 * @return bool
	 */
	function hasSessionCookie() {
		return (isset($_COOKIE[session_name()]));
	}

	/**
	 * Returns true if the session has started
	 * @return bool
	 */
	function isStarted() {
		return isset($_SESSION);
	}

}