<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/28/2014
 * Time: 3:08 PM
 */
namespace CPath\Render\HTML\Header;

class HeaderTagReader implements IHeaderWriter
{
	private $mTags = array();

	public function getTags() {
		return $this->mTags;
	}

	public function hasTag($tagName) {
		return isset($this->mTags[$tagName]);
	}

	public function getTag($tagName) {
		return $this->mTags[$tagName];
	}

	/**
	 * Write a header as raw html
	 * Note: Uniqueness of html is not checked. String will be written every time
	 * @param String $html
	 * @return IHeaderWriter return inst of self
	 */
	function writeHTML($html) {
		if (preg_match('/<meta([^>]+)\/?>/i', $html, $matches)) {
			$x                               = new \SimpleXMLElement('<meta ' . $matches[1] . '/>');
			$this->mTags[(string)$x['name']] = (string)$x['content'];
		}
	}

	/**
	 * Write a <script> header only the first time it's encountered
	 * @param String $scriptPath the script url
	 * @param bool $defer
	 * @param null $charset
	 * @return IHeaderWriter return inst of self
	 */
	function writeScript($scriptPath, $defer = false, $charset = null) {
	}

	/**
	 * Write a <link type="text/css"> header only the first time it's encountered
	 * @param String $styleSheetPath the stylesheet url
	 * @return IHeaderWriter return inst of self
	 */
	function writeStyleSheet($styleSheetPath) {
	}
}