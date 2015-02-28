<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/3/14
 * Time: 6:25 PM
 */
namespace CPath\Response\Common;

use CPath\Data\Map\IKeyMap;
use CPath\Data\Map\IKeyMapper;
use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Response\IResponse;
use CPath\Response\Response;
use CPath\Response\ResponseRenderer;
use CPath\Route\RouteLink;

class RedirectResponse extends Response implements IKeyMap, IRenderHTML
{
	const STR_REDIRECT = 'redirect';
	const STR_TIMEOUT = 'timeout';

	private $mPath;
	private $mRedirectURL;
	private $mTimeout;

	/**
	 * @param null $redirectURL
	 * @param string $message
	 * @param null $timeout
	 */
	function __construct($redirectURL, $message = null, $timeout = null) {
		parent::__construct($message);
		$this->mRedirectURL = $redirectURL;
		$this->mTimeout = $timeout;
		$this->mPath = $redirectURL;
	}

	public function getRedirectURL(IRequest $Request) {
		$url = $this->mRedirectURL;
		$domainPath = $Request->getDomainPath();
		if(strpos($url, $domainPath) === false)
			$url = $domainPath . ltrim($url, '/');
		return $url;
	}

	/**
	 * Send response headers for this response
	 * @param IRequest $Request
	 * @param string $mimeType
	 * @return bool returns true if the headers were sent, false otherwise
	 */
	function sendHeaders(IRequest $Request, $mimeType = null) {
		parent::sendHeaders($Request, $mimeType);

		$url = $this->getRedirectURL($Request);

		if ($this->mTimeout === null) {
			header('Location: ' . $url);

		} else {
			header('Refresh ' .  $this->mTimeout . '; URL=' . $url);

		}

	}

	/**
	 * Render request as html
	 * @param IRequest $Request the IRequest inst for this render which contains the request and remaining args
	 * @param IAttributes $Attr
	 * @param IRenderHTML $Parent
	 * @return String|void always returns void
	 */
	function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
		static $urlCount = 0;
		$id = 'url.' . $urlCount++;
		$url = $this->getRedirectURL($Request);
		$urlHTML = '<a id="' . $id . '" href="' . $url . '">' . $this->mRedirectURL . '</a>';
		if($this->mTimeout) {
			echo RI::ni(), '<div class="info">Redirecting to ', $urlHTML, ' in ', $this->mTimeout, ' seconds</div>';
			echo RI::ni();
			$t = $this->mTimeout;
			echo <<<HTML
<script>
	var t = {$t};
	for(var i=0; i<=t; i++)
		setTimeout(function() {
			var l = document.getElementById("{$id}");
			l.parentElement.innerHTML =
			l.parentElement.innerHTML.replace(/\d+ seconds/i, t-- + ' seconds');
		}, i * 1000);
	setTimeout(function() {
		var l = document.getElementById("{$id}");
		l.click();
	}, t * 1000);
</script>
HTML;

		} else {
			echo RI::ni(), '<div class="info">Redirecting to ', $urlHTML, '</div>';
		}

		$ResponseRenderer = new ResponseRenderer($this);
		$ResponseRenderer->renderHTML($Request, $Attr, $this);
	}

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map inst to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
	function mapKeys(IKeyMapper $Map) {
        parent::mapKeys($Map);
		$Map->map(static::STR_REDIRECT, new RouteLink($this->mRedirectURL, $this->mRedirectURL));
		if($this->mTimeout !== null)
			$Map->map(static::STR_TIMEOUT, $this->mTimeout);
	}

}