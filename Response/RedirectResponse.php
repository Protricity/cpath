<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/1/14
 * Time: 1:45 AM
 */
namespace CPath\Response;

class RedirectResponse extends Response
{
	private $mURI;

	public function __construct($uri, $message = null, $status = true) {
		parent::__construct($message, $status);
		$this->mURI = $uri;
	}


}