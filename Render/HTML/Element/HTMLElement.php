<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/14
 * Time: 3:02 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\HTMLHeaders;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLContainer;
use CPath\Render\HTML\HTMLContent;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use Traversable;

class HTMLElement extends AbstractHTMLElement implements IHTMLContainer, \IteratorAggregate, \ArrayAccess
{
	const ALLOW_CLOSED_TAG = true;
    /** @var HTMLContainer */
    private $mContent;
	/** @var IHTMLContainer */
	private $mItemTemplate = null;

	/** @var IHTMLSupportHeaders[] */
	private $mSupportHeaders = array();

    /**
     * @param string $elmType
     * @param String|Array|IAttributes $classList attribute instance, class list, or attribute html
     * @param String|null $_content [optional] varargs of content
     */
    public function __construct($elmType = 'div', $classList = null, $_content = null) {
	    if($classList instanceof IHTMLSupportHeaders)
		    $this->addSupportHeaders($classList);

	    parent::__construct($elmType, $classList);

	    $this->mContent = new HTMLContainer();

        if($_content !== null)
            for($i=2;$i<func_num_args();$i++)
	            $this->addAll(func_get_arg($i));
    }

	public function addSupportHeaders(IHTMLSupportHeaders $Headers) {
		$this->mSupportHeaders[] = $Headers;
	}

	public function setItemTemplate(IHTMLContainer $Template) {
		$this->mItemTemplate = $Template;
	}

	/**
	 * Add IRenderHTML Content
	 * @param IRenderHTML $Render
	 * @param null $key if provided, add/replace content by key
	 * @return void always returns void
	 */
    function addContent(IRenderHTML $Render, $key=null) {
	    $this->mContent->addContent($Render, $key);
    }

//	/**
//	 * Add header content
//	 * @param IHTMLSupportHeaders $Headers
//	 */
//	public function addHeaders(IHTMLSupportHeaders $Headers) {
//		$this->addContent(new HTMLHeaders($Headers));
//	}

	/**
	 * Add any kind of content
	 * @param $content
	 * @param null $_content
	 */
	function addAll($content, $_content=null) {
		foreach(func_get_args() as $arg) {
			if(is_array($arg)) {
				foreach($arg as $a)
					$this->addAll($a);
			} else {
				$this[] = $arg;
			}
		}
	}

	/**
	 * Returns true if content is available and should render
	 * @param null $key if provided, returns true if content at this key index exists
	 * @return bool
	 */
	function hasContent($key=null) {
		return $this->mContent->addContent($key);
	}

	/**
	 * Returns an array of IRenderHTML content
	 * @param null $key if provided, get content by key
	 * @return IRenderHTML[]
	 * @throws \InvalidArgumentException if content at $key was not found
	 */
	public function getContent($key=null) {
		return $this->mContent->getContent($key);
	}

	/**
	 * Remove content
	 * @param null $key if provided, removes content at key, if exists
	 * @return int the number of items removed
	 */
	function removeContent($key = null) {
		return $this->mContent->removeContent($key);
	}

	/**
	 * Write all support headers used by this IView instance
	 * @param IRequest $Request
	 * @param \CPath\Framework\Render\Header\IHeaderWriter $Head the writer instance to use
	 * @return void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$this->mContent->writeHeaders($Request, $Head);
		foreach($this->mSupportHeaders as $Headers)
			$Headers->writeHeaders($Request, $Head);
	}

    /**
     * Render element content
     * @param IRequest $Request
     * @param IAttributes $ContentAttr
     */
    function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
	    RI::ai(1);

	    $Content = $this->getContent();
	    $newLine = sizeof($Content) > 1;
        foreach($Content as $ContentItem) {
	        $this->renderContentItem($Request, $ContentItem, $ContentAttr);
	        if(!$ContentItem instanceof HTMLContent)
	            $newLine = true;
        }

	    RI::ai(-1);

	    if($newLine)
		    echo RI::ni();
    }

	protected function renderContentItem(IRequest $Request, IRenderHTML $Content, IAttributes $ContentAttr = null) {
		if($this->mItemTemplate) {
			$this->mItemTemplate->removeContent();
			$this->mItemTemplate->addContent($Content);
			$this->mItemTemplate->renderHTML($Request);
		} else {
			$Content->renderHTML($Request, $ContentAttr);
		}
	}

	/**
	 * Returns true if this element has an open tag
	 * @return bool
	 */
	protected function isOpenTag() {
		return !self::ALLOW_CLOSED_TAG || sizeof($this->mContent) > 0;
	}

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->mContent);
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
        return isset($this->mContent[$offset]);
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
        return $this->mContent[$offset];
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
	    if(!$value instanceof IRenderHTML)
		    $value = new HTMLContent($value);
        $this->addContent($value, $offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->mContent[$offset]);
    }
}

