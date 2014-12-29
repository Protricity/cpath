<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/21/14
 * Time: 7:21 PM
 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Common\HTMLText;
use CPath\Render\HTML\Header\HTMLMetaTag;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Request\IRequest;
use Traversable;

abstract class AbstractHTMLContainer implements IHTMLContainer, \ArrayAccess, \IteratorAggregate
{
	private $mHeaders = null;

	/**
	 * Add support headers to content
	 * @param IHTMLSupportHeaders $Headers
	 * @param IHTMLSupportHeaders $_Headers [vararg]
	 * @return void
	 */
	public function addSupportHeaders(IHTMLSupportHeaders $Headers, IHTMLSupportHeaders $_Headers=null) {
		$this->getSupportHeaders();
		foreach(func_get_args() as $Headers)
			$this->getSupportHeaders()->addSupportHeaders($Headers);
	}


	/**
	 * @return HTMLHeaderContainer
	 */
	function getSupportHeaders() {
		return $this->mHeaders ?: $this->mHeaders = new HTMLHeaderContainer();
	}

	/**
	 * Get meta tag content or return null
	 * @param String $name tag name
	 * @return String|null
	 */
	function getMetaTagContent($name) {
		return $this->getSupportHeaders()->getMetaTagContent($name);
	}

	/**
	 * Write all support headers used by this renderer
	 * @param IRequest $Request
	 * @param IHeaderWriter $Head the writer inst to use
	 * @return void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$this->getSupportHeaders()->writeHeaders($Request, $Head);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Retrieve an external iterator
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An inst of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIterator() {
		return new \ArrayIterator($this->getContent());
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset) {
		$Content = $this->getContent();

		return isset($Content[$offset]);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		$Content = $this->getContent();

		return $Content[$offset];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if (!$value instanceof IRenderHTML)
			$value = new HTMLText($value);
		$this->addContent($value, $offset);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->removeContent($offset);
//		throw new \InvalidArgumentException("May not unset elements in " . get_class($this));
		//unset($this->mContent[$offset]);
	}
}