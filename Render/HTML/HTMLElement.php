<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/2/14
 * Time: 3:02 PM
 */
namespace CPath\Render\HTML;

use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\ISupportHeaders;
use CPath\Render\HTML\IContainerHTML;
use CPath\Render\HTML\IRenderHTML;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;

class HTMLElement implements IContainerHTML, ISupportHeaders
{
    private $mElmType;
    private $mAttr;
    /** @var IRenderHTML[] */
    private $mContent = array();

    /**
     * @param string $elmType
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr
     * @param IRenderHTML|string $_content varargs for content (strings allowed)
     */
    public function __construct($elmType = 'div', IAttributes $Attr = null, $_content = null) {
        $this->mElmType = $elmType;
        $this->mAttr = $Attr;
        for($i=2;;$i++)
            if($Content = func_get_arg($i))
                if($Content instanceof IRenderHTML)
                    $this->addContent(func_get_arg($i));
                else
                    $this->addContent(new HTMLContent($Content));
    }

    /**
     * Add HTML Container Content
     * @param IRenderHTML|string $Content
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Content) {
        $this->mContent[] = $Content;
    }

    /**
     * Render request as html
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return String|void always returns void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        if(!$this->mContent) {
            echo RI::ni(), "<", $this->mElmType, $Attr->renderHTML($Request, $Attr), "/>";
        } else {
            echo RI::ni(), "<", $this->mElmType, $Attr->renderHTML($Request, $Attr), ">";
            RI::ai(1);

            foreach($this->mContent as $Content)
                $Content->renderHTML($Request);

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
            if($Content instanceof ISupportHeaders)
                $Content->writeHeaders($Request, $Head);
    }
}

