<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/14
 * Time: 3:02 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\Helpers\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\Header\IHeaderWriter;
use CPath\Render\HTML\Header\IHTMLSupportHeaders;
use CPath\Render\HTML\HTMLContainer;
use CPath\Render\HTML\IHTMLContainer;
use CPath\Render\HTML\IRenderHTML;
use CPath\Render\HTML\RenderCallback;
use CPath\Request\IRequest;
use CPath\Request\Validation\IValidation;
use Traversable;

class HTMLElement extends AbstractHTMLElement implements IHTMLContainer
{
	const ALLOW_CLOSED_TAG = true;
    const DEFAULT_CONTAINER_KEY = '#default';

	/** @var HTMLContainer[] */
	private $mContainers = array();

	/** @var IHTMLContainer */
	private $mItemTemplate = null;
    /** @var IRenderHTML[] */
    private $mSpecial = array();

    /**
	 * @param string $elmType
	 * @param String|null $classList a list of class elements
	 * @param String|null|Array|IAttributes|IValidation $_content [varargs] attribute html as string, array, or IValidation || IAttributes instance
	 */
    public function __construct($elmType, $classList = null, $_content = null) {
	    $this->mContainers[self::DEFAULT_CONTAINER_KEY] = new HTMLContainer();
        $this->mContainers[self::DEFAULT_CONTAINER_KEY]->onContentAdded($this);
        parent::__construct($elmType);

	    is_scalar($classList)   ? $this->addClass($classList) : $this->addVarArg($classList);

	    for($i=2; $i<func_num_args(); $i++)
		    $this->addVarArg(func_get_arg($i));
    }

	protected function addVarArg($arg) {
		if(is_array($arg)) {
			foreach($arg as $a)
				$this->addVarArg($a);
		} elseif ($arg instanceof \Closure){
			$this->addContent(new RenderCallback($arg));
		} elseif (is_string($arg)){
			$this[] = $arg;
		} else if ($arg instanceof IRenderHTML){
			$this[] = $arg;
		} else {
			parent::addVarArg($arg, false);
		}
	}

    /**
     * @param string $key
     * @return IHTMLContainer
     */
	public function getContainer($key = null) {
        $key ?: $key = self::DEFAULT_CONTAINER_KEY;
        if(isset($this->mContainers[$key]))
            return $this->mContainers[$key];
        throw new \InvalidArgumentException("Container not found: " . $key);
	}

	public function setContainer(IHTMLContainer $Container, $key = self::DEFAULT_CONTAINER_KEY) {
		foreach($this->getContainer()->getContentRecursive() as $Content) {
			if($Container === $Content) {
				$this->mContainers[$key] = $Content;
				return;
			}
		}
		throw new \InvalidArgumentException("Container not found: " . get_class($Container));
	}


	public function setItemTemplate(IHTMLContainer $Template) {
		$this->mItemTemplate = $Template;
	}

    /**
     * @param $selector
     * @param string $key
     * @return IHTMLElement[]
     */
    function matchElements($selector, $key=null) {
        return $this->getContainer($key)->matchElements($selector);
    }

    /**
     * @param $selector
     * @param string $key
     * @return IHTMLElement
     */
    function matchElement($selector, $key=null) {
        $Matches = $this->matchElements($selector, $key);
        if(!empty($Matches[0]))
            throw new \InvalidArgumentException("Selector did not match any elements: " . $selector);
        return $Matches[0];
    }

    /**
	 * Add renderable content
	 * @param IRenderHTML $Render
	 * @param null $key if provided, add/replace content by key
	 * @return void always returns void
	 */
	function addContent(IRenderHTML $Render, $key=null) {
        switch($key) {
            case IHTMLContainer::KEY_RENDER_CONTENT_BEFORE:
            case IHTMLContainer::KEY_RENDER_CONTENT_AFTER:
                $this->mSpecial[$key] = $Render;
                break;
            default:
                $this->getContainer()->addContent($Render, $key);
                break;
        }
	}

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
		return $this->getContainer()->hasContent($key);
	}

    /**
     * Returns an array of IRenderHTML content
     * @param null|string $key if provided, get content by key
     * @return IRenderHTML[]
     */
	public function getContent($key=null) {
        $key ?: $key = self::DEFAULT_CONTAINER_KEY;
        return $this->getContainer($key)->getContent();
	}
	/**
	 * Returns a single dimension array containing all content
	 * @return IRenderHTML[]
	 * @throws \InvalidArgumentException if content at $key was not found
	 */
	public function getContentRecursive() {
		return $this->getContainer()->getContentRecursive();
	}

	/**
	 * Remove content
	 * @param null $key if provided, removes content at key, if exists
	 * @return int the number of items removed
	 */
	function removeContent($key = null) {
		return $this->getContainer()->removeContent($key);
	}

	/**
	 * Write all support headers used by this IView inst
	 * @param IRequest $Request
	 * @param \CPath\Render\HTML\Header\IHeaderWriter $Head the writer inst to use
	 * @return void always returns void
	 */
	function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $this->getContainer()->writeHeaders($Request, $Head);
        foreach($this->mSpecial as $Special)
            if($Special instanceof IHTMLSupportHeaders)
                $Special->writeHeaders($Request, $Head);

		parent::writeHeaders($Request, $Head);

		foreach($this->getAttributes($Request) as $Attr)
			if($Attr instanceof IHTMLSupportHeaders)
				$Attr->writeHeaders($Request, $Head);
//
//		foreach($this->getClasses() as $class) {
//			$selector = $this->getElementType() . '.' . $class;
//			HTMLThemeConfig::writeThemeHeaders($Request, $Head, $selector);
//		}
	}

    function renderHTML(IRequest $Request, IAttributes $Attr = null, IRenderHTML $Parent = null) {
        if(isset($this->mSpecial[IHTMLContainer::KEY_RENDER_CONTENT_BEFORE]))
            $this->mSpecial[IHTMLContainer::KEY_RENDER_CONTENT_BEFORE]->renderHTML($Request);
        parent::renderHTML($Request, $Attr, $Parent);
        if(isset($this->mSpecial[IHTMLContainer::KEY_RENDER_CONTENT_AFTER]))
            $this->mSpecial[IHTMLContainer::KEY_RENDER_CONTENT_AFTER]->renderHTML($Request);
    }

    /**
	 * Render element content
	 * @param IRequest $Request
	 * @param IAttributes $ContentAttr
	 * @param \CPath\Render\HTML\IHTMLContainer|\CPath\Render\HTML\IRenderHTML $Parent
	 */
    function renderContent(IRequest $Request, IAttributes $ContentAttr = null, IRenderHTML $Parent = null) {
	    RI::ai(1);

	    $Content = $this->getContainer()->getContent();
        foreach($Content as $index => $ContentItem)
	        $this->renderContentItem($Request, $index, $ContentItem, $ContentAttr);

	    RI::ai(-1);

	    if(!$this->hasFlag(self::FLAG_SKIP_NEWLINE) && sizeof($Content) > 1)
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
		return !self::ALLOW_CLOSED_TAG || sizeof($this->getContainer()) > 0;
	}

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An inst of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->getContainer()->getContent());
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
	    return $this->getContainer()->offsetExists($offset);
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
	    return $this->getContainer()->offsetGet($offset);
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
    public function     offsetSet($offset, $value) {
	    $this->getContainer()->offsetSet($offset, $value);
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
	    $this->getContainer()->offsetUnset($offset);
    }
}

