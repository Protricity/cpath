<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/29/14
 * Time: 4:12 PM
 */
namespace CPath\Request\Exceptions;

use CPath\Response\IResponse;
use CPath\Response\Response;

class RedirectException extends RequestException
{
	private $mPath;

	/**
	 * @param null $redirectURL
	 * @param string $message
	 * @param null $timeout
	 */
	function __construct($redirectURL, $message=null, $timeout=null) {
		parent::__construct($message, IResponse::HTTP_TEMPORARY_REDIRECT);
		$this->mPath = $redirectURL;

		if($timeout === null) {
			$this->addHeader('Location', $redirectURL);

		} else {
			$this->addHeader('Refresh', $timeout . '; URL=' . $redirectURL);

		}
	}

	/**
	 * Return the redirection url
	 * @return String
	 */
	function getLocationURL() {
		return $this->mPath;
	}
}
