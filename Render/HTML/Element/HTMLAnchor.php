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

class HTMLAnchor extends AbstractHTMLContainer
{
	const ALLOW_CLOSED_TAG = false;
	const TRIM_CONTENT = true;
	private $mText;

	/**
	 * @param string $href
	 * @param string|null $text
	 * @param string|\CPath\Render\HTML\Attribute\IAttributes $attr
	 */
    public function __construct($href, $text=null, $attr=null) {
        parent::__construct('a', $attr);
	    $this->mText = $text;
        $this->setURL($href);
    }

	public function getURL()           { return $this->getAttribute('href'); }
	public function setURL($value)     { $this->setAttribute('href', $value); }

	public function setContent($text)  { $this->mText = $text; }
	public function getContent()       { return $this->mText; }

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 */
	protected function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
		if($this->mText instanceof IRenderHTML)
			$this->mText->renderHTML($Request, $ContentAttr);
		else
			echo $this->mText;
	}

	/**
	 * Returns true if content is available and should render
	 * @internal param \CPath\Request\IRequest $Request
	 * @return bool
	 */
	protected function hasContent() {
		return $this->mText !== null;
	}
}