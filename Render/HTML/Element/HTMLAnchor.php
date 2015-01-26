<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 12:58 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Data\Value\IHasURL;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLAnchor extends AbstractHTMLElement implements IHasURL
{
	const ALLOW_CLOSED_TAG = false;

	private $mText;
	private $mURL;

	/**
	 * @param string $href
	 * @param string|null $text
	 * @param string|null $classList
	 */
    public function __construct($href, $text=null, $classList = null) {
        parent::__construct('a', $classList);
	    $this->mText = $text;
	    $this->mURL = $href;
    }

	/**
	 * Render html attributes
	 * @param IRequest|null $Request
	 * @internal param bool $return
	 * @return string|void always returns void
	 */
	function renderHTMLAttributes(IRequest $Request=null) {
		parent::renderHTMLAttributes($Request);
		echo ' href="', str_replace('"', "'", $this->getURL($Request)), '"';
	}

	public function getURL(IRequest $Request=null) {
		if($Request) {
			$domainPath = $Request->getDomainPath(true);
			if(strpos($this->mURL, $domainPath) === false)
				return $domainPath . ltrim($this->mURL, '/');
		}
		return $this->mURL;
	}

	public function setURL($href) {
		$this->mURL = $href;
		return $this;
	}

	public function setText($text) {
		$this->mText = $text;
		return $this;
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IRenderHTML $Parent
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
		if($this->mText instanceof IRenderHTML)
			$this->mText->renderHTML($Request, $ContentAttr);
		else
			echo $this->mText;
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return true;
	}
}