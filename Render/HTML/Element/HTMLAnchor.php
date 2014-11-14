<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/30/14
 * Time: 12:58 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;

class HTMLAnchor extends HTMLElement
{
	const ALLOW_CLOSED_TAG = false;
	const TRIM_CONTENT = true;
	private $mText;

	/**
	 * @param string $href
	 * @param string|null $text
	 * @param null $classList
	 */
    public function __construct($href, $text=null, $classList = null) {
        parent::__construct('a', $classList);
	    $this->mText = $text;
        $this->setURL($href);
    }

	public function getURL()           { return $this->getAttribute('href'); }
	public function setURL($value)     { $this->setAttribute('href', $value); }

	public function setContent($text)  { $this->mText = $text; }
	public function getContent($key=null)       { return $this->mText; }

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
		if($this->mText instanceof IRenderHTML)
			$this->mText->renderHTML($Request, $ContentAttr);
		else
			echo $this->mText;
	}

	/**
	 * Returns true if content is available and should render
	 * @param null $key
	 * @return bool
	 */
	function hasContent($key=null) {
		return $this->mText !== null;
	}

	/**
	 * Add IRenderHTML Content
	 * @param IRenderHTML $Render
	 * @param null $key if provided, add/replace content by key
	 * @return void
	 */
	function addContent(IRenderHTML $Render, $key = null) {
		$this->mText = $Render;
	}
}