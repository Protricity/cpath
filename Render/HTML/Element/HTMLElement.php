<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/14
 * Time: 3:02 PM
 */
namespace CPath\Render\HTML\Element;

use CPath\Render\HTML\Attribute\HTMLAttributes;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute;
use CPath\Render\HTML\Common\HTMLText;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use Traversable;

class HTMLElement implements IRenderHTML, IHTMLSupportHeaders, \IteratorAggregate, \ArrayAccess
{
    private $mElmType;
    private $mAttr;
    /** @var IRenderHTML[] */
    private $mContent = array();

    /**
     * @param string $elmType
     * @param String|\CPath\Render\HTML\Attribute\IAttributes $attr
     * @param String|null $_content [optional] varargs of content
     */
    public function __construct($elmType = 'div', $attr = null, $_content = null) {
        $this->mElmType = $elmType;
        $this->mAttr = $attr instanceof IAttributes ? $attr : new HTMLAttributes($attr);
        if($_content !== null)
            for($i=2;$i<func_num_args();$i++)
                if($Content = func_get_arg($i))
                    if($Content instanceof IRenderHTML)
                        $this->addContent($Content);
                    else
                        $this->addContent(new HTMLText($Content));
    }

    function getElementType() {
        return $this->mElmType;
    }

    function setAttribute($attrName, $attrValue) {
        $this->mAttr->setAttribute($attrName, $attrValue);
    }

    function getAttribute($attrName) {
        return $this->mAttr->getAttribute($attrName);
    }

    public function addClass($classList) {
        $this->mAttr->addClass($classList);
    }

    /**
     * @return IAttributes
     */
    public function getAttributes() {
        return $this->mAttr;
    }

    /**
     * Add HTML Container Content
     * @param IRenderHTML|string $Content
     * @param null $key
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Content, $key=null) {
        if($key !== null)
            $this->mContent[$key] = $Content;
        else
            $this->mContent[] = $Content;
//        if($Attr) {
//            end($this->mContent);
//            $i = key($this->mContent);
//            $this->mContentAttr[$i] = $Attr;
//        }
    }

    /**
     * Remove an IRenderHTML instance from the container
     * @param IRenderHTML $Content
     * @return bool true if the content was found and removed
     */
    function removeContent(IRenderHTML $Content) {
        foreach($this->mContent as $key => $C) {
            if($C === $Content) {
                unset($this->mContent[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * Render element content
     * @param IRequest $Request
     * @param IAttributes $ContentAttr
     */
    protected function renderContent(IRequest $Request, IAttributes $ContentAttr = null) {
        foreach($this->mContent as $Render)
            $Render->renderHTML($Request, $ContentAttr);
    }

    /**
     * Render request as html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null) {
        $Attr = $this->mAttr->merge($Attr);
        if(!$this->mContent) {
            echo RI::ni(), "<", $this->mElmType, $Attr, "/>";
        } else {
            echo RI::ni(), "<", $this->mElmType, $Attr, ">";
            RI::ai(1);

            $this->renderContent($Request);

            RI::ai(-1);
            echo RI::ni(), "</", $this->mElmType, ">";
        }
    }

    /**
     * Write all support headers used by this IView instance
     * @param IRequest $Request
     * @param \CPath\Framework\Render\Header\IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        foreach($this->mContent as $Content)
            if($Content instanceof IHTMLSupportHeaders)
                $Content->writeHeaders($Request, $Head);
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

