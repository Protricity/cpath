<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/4/14
 * Time: 5:24 PM
 */
namespace CPath\Render\HTML;

use CPath\Describable\IDescribable;
use CPath\Framework\Render\Header\IHTMLSupportHeaders;
use CPath\Framework\Render\Header\WriteOnceHeaderRenderer;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Request\IRequest;

class HTMLResponseBody implements IHTMLTemplate
{
    const DOCTYPE = '<!DOCTYPE html>';
    const TAB = '  ';
    const TAB_START = 0;

    /** @var IRenderHTML[] */
    //private $mContent=array();

    public function __construct() {
//        foreach(func_get_args() as $arg)
//            if($arg)
//                $this->addContent($arg);
    }

    /**
     * Render the view body html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IRenderHTML $Content
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTMLContent(IRequest $Request, IRenderHTML $Content, IAttributes $Attr = null)
    {
        RI::si(static::TAB_START, static::TAB);
        echo self::DOCTYPE;

        echo RI::ni(), '<html>';
        RI::ai(1);

            echo RI::ni(), '<head>';
            RI::ai(1);

                $this->renderHTMLHeaders($Request, $Content);

            RI::ai(-1);
            echo RI::ni(), '</head>';


            echo RI::ni(), '<body', $Attr, '>';
            RI::ai(1);

                $Content->renderHTML($Request);

//                foreach($this->mContent as $Content)
//                    $Content->renderHTML($Request, $Attr);

            RI::ai(-1);
            echo RI::ni(), '</body>';

        RI::ai(-1);
        echo RI::ni(), '</html>';
    }

	/**
	 * Render HTML header html
	 * @param \CPath\Request\IRequest $Request
	 * @param IRenderHTML $Content
	 * @return WriteOnceHeaderRenderer the writer instance used
	 */
    protected function renderHTMLHeaders(IRequest $Request, IRenderHTML $Content) {
        $Writer = new WriteOnceHeaderRenderer();

        if ($Content instanceof IDescribable) {
            $title = $Content->getTitle();
            echo RI::ni(), "<title>", $title, "</title>";
        }

        if ($this instanceof IHTMLSupportHeaders)
            $this->writeHeaders($Request, $Writer);

        if ($Content instanceof IHTMLSupportHeaders)
            $Content->writeHeaders($Request, $Writer);

        return $Writer;
    }
//
//    /**
//     * Add HTML Container Content
//     * @param IRenderHTML $Content
//     * @return String|void always returns void
//     */
//    function addContent(IRenderHTML $Content) {
//        $this->mContent[] = $Content;
//    }
//    /**
//     * Remove an IRenderHTML instance from the container
//     * @param IRenderHTML $Content
//     * @return bool true if the content was found and removed
//     */
//    function removeContent(IRenderHTML $Content) {
//        foreach($this->mContent as $i => $C)
//            if($C === $Content) {
//                unset($this->mContent[$i]);
//                return true;
//            }
//        return false;
//    }

//    /**
//     * Render this request
//     * @param IRequest $Request the IRequest instance for this render
//     * @return String|void always returns void
//     */
//    function render(IRequest $Request) {
//        $Renderer = new RenderMimeSwitchUtility($this);
//        try {
//            $Renderer->render($Request);
//        } catch (\Exception $ex) {
//            $Response = new ExceptionResponse($ex);
//            $ResponseUtil = new ResponseUtil($Response);
//            $ExceptionRenderer = new RenderMimeSwitchUtility($ResponseUtil);
//            $ExceptionRenderer->render($Request);
//        }
//    }
}