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
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLContainer;
use CPath\Render\HTML\HTMLContent;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\Theme\HTMLThemeConfig;
use CPath\Request\IRequest;
use Traversable;

class HTMLElement extends AbstractHTMLElement implements IHTMLContainer, \IteratorAggregate, \ArrayAccess
{
	const ALLOW_CLOSED_TAG = true;

	/** @var HTMLContainer */
	private $mContent;

	/** @var HTMLContainer */
	private $mContainer;

	/** @var IHTMLContainer */
	private $mItemTemplate = null;

	/** @var IHTMLSupportHeaders[] */
	private $mSupportHeaders = array();

    /**
     * @param string $elmType
     * @param String|Array|IAttributes $classList attribute inst, class list, or attribute html
     * @param String|null $_content [optional] varargs of content
     */
    public function __construct($elmType = 'div', $classList = null, $_content = null) {
	    parent::__construct($elmType, $classList);

	    $this->mContainer =
	    $this->mContent = new HTMLContainer();

        if($_content !== null)
            for($i=2;$i<func_num_args();$i++)
	            $this->addAll(func_get_arg($i));
    }

	public function getContainer() {
		return $this->mContainer;
	}

	public function setContainer(IHTMLContainer $Container) {
		foreach($this->getContainer()->getContent() as $Content) {
			if($Container === $Content) {
				$this->mContainer = $Content;
				return;
			}
		}
		throw new \InvalidArgumentException("Container not found: " . get_class($Container));
	}

	/**
	 * Add support headers to content
	 * @param IHTMLSupportHeaders $Headers
	 * @param IHTMLSupportHeaders $_Headers [vararg]
	 * @return void
	 */
	public function addSupportHeaders(IHTMLSupportHeaders $Headers, IHTMLSupportHeaders $_Headers=null) {
		foreach(func_get_args() as $Headers) {
			$this->mSupportHeaders[] = $Headers;
		}
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
	    $this->mContainer->addContent($Render, $key);
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
		return $this->mContainer->addContent($key);
	}

	/**
	 * Returns an array of IRenderHTML content
	 * @param null $key if provided, get content by key
	 * @return IRenderHTML[]
	 * @throws \InvalidArgumentException if content at $key was not found
	 */
	public function getContent($key=null) {
		return $this->mContainer->getContent($key);
	}

	/**
	 * Remove content
	 * @param null $key if provided, removes content at key, if exists
	 * @return int the number of items removed
	 */
	function removeContent($key = null) {
		return $this->mContainer->removeContent($key);
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param \CPath\Framework\Render\Header\IHeaderWriter $Head the writer inst to use
	 * @return void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
		$this->mContent->writeHeaders($Request, $Head);

		foreach($this->mSupportHeaders as $Headers)
			$Headers->writeHeaders($Request, $Head);

		if($Additional = $this->getAdditionalAttributes())
			foreach($Additional as $Attribute)
				if($Attribute instanceof IHTMLSupportHeaders)
					$Attribute->writeHeaders($Request, $Head);

		foreach($this->getClasses() as $class) {
			$selector = $this->getElementType() . '.' . $class;
			HTMLThemeConfig::writeThemeHeaders($Request, $Head, $selector);
		}
	}

	/**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
	 */
    function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
	    RI::ai(1);

	    $Content = $this->mContent->getContent();
	    $newLine = sizeof($Content) > 1;
        foreach($Content as $index => $ContentItem) {
	        $this->renderContentItem($Request, $index, $ContentItem, $ContentAttr);
	        if(!$ContentItem instanceof HTMLContent)
	            $newLine = true;
        }

	    RI::ai(-1);

	    if($newLine)
		    echo RI::ni();
    }

	/**
	 * Render content item
	 * @param IRequest $Request
	 * @param $index
	 * @param IRenderHTML $Content
	 * @param IAttributes $ContentAttr
	 */
	protected function renderContentItem(IRequest $Request, $index, IRenderHTML $Content, IAttributes $ContentAttr = null) {
		if($Template = $this->mItemTemplate) {
			$Template->removeContent();
			$Template->addContent($Content, is_int($index) ? null : $index);
			$Template->renderHTML($Request);
		} else {
			$Content->renderHTML($Request, $ContentAttr, $this);
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
     * @return Traversable An inst of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->mContainer->getContent());
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
	    return $this->mContainer->offsetExists($offset);
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
	    return $this->mContainer->offsetGet($offset);
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
	    $this->mContainer->offsetSet($offset, $value);
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
	    $this->mContainer->offsetUnset($offset);
    }
}

