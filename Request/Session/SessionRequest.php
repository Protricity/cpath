<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/16/2015
 * Time: 9:43 AM
 */
namespace CPath\Request\Session;


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
	 * @throws SessionRequestException
	 * @return bool true if session was started, otherwise false
	 */
	function startSession() {
		if(isset($_SESSION))
			throw new SessionRequestException("Session already active");

        if(headers_sent($file, $line))
            throw new SessionRequestException("Headers already sent {$file}:{$line}");

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
		session_unset();
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

		session_start();
        session_regenerate_id();
		if(!session_destroy())
			throw new SessionRequestException("Could not destroy session");

		setcookie( session_name(), "", time()-3600, "/" );
		return true;
	}

    /**
     * Returns the session id or false if inactive
     * @return string|bool
     */
	function getSessionID() {
		return isset($_COOKIE[session_name()]) ? $_COOKIE[session_name()] : session_id();
	}

	/**
	 * Returns true if the session has started
	 * @return bool
	 */
	function isStarted() {
		return isset($_SESSION);
	}

}