<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/4/14
 * Time: 5:24 PM
 */
namespace CPath\Render\HTML;

use CPath\Describable\IDescribable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Header\WriteOnceHeaderRenderer;
use CPath\Framework\Render\Header\ISupportHeaders;
use CPath\Render\HTML\IContainerHTML;
use CPath\Render\HTML\IRenderHTML;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;

class HTMLResponseBody implements IContainerHTML
{

    /** @var IRenderHTML[] */
    private $mContent=array();

    public function __construct(IRenderHTML $_Content=null) {
        foreach(func_get_args() as $arg)
            if($arg)
                $this->addContent($arg);
    }

//    //** todo? right place for this? */
//    protected function sendHeaders($message = NULL, $code = NULL, $mimeType = NULL)
//    {
//        if (!headers_sent()) {
//            $message = $message ? preg_replace('/[^\w -]/', '', $message) : static::RESPONSE_MESSAGE;
//            header("HTTP/1.1 " . ($code ? : static::RESPONSE_CODE) . " " . $message);
//            if (static::RESPONSE_MIMETYPE)
//                header("Content-Type: " . ($mimeType ? : static::RESPONSE_MIMETYPE));
//        }
//    }

    /**
     * Render the view body html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param \CPath\Render\HTML\Attribute\IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        RI::si(static::TAB_START, static::TAB);

        // $this->sendHeaders();

        echo RI::ni(), '<html>';
        RI::ai(1);

            echo RI::ni(), '<head>';
            RI::ai(1);

                $this->renderHTMLHeaders($Request);

            RI::ai(-1);
            echo RI::ni(), '</head>';


            echo RI::ni(), '<body>';
            RI::ai(1);

                foreach($this->mContent as $Content)
                    $Content->renderHTML($Request, $Attr);

            RI::ai(-1);
            echo RI::ni(), '</body>';

        RI::ai(-1);
        echo RI::ni(), '</html>';
    }

    /**
     * Render HTML header html
     * @param \CPath\Request\IRequest $Request
     * @return WriteOnceHeaderRenderer the writer instance used
     */
    protected function renderHTMLHeaders(IRequest $Request)
    {
        $Writer = new WriteOnceHeaderRenderer();

        if ($this instanceof IDescribable) {
            $title = $this->getTitle();
            echo RI::ni(), "<title>", $title, "</title>";
        }

        if ($this instanceof ISupportHeaders)
            $this->writeHeaders($Request, $Writer);

        foreach($this->mContent as $Content)
            if ($Content instanceof ISupportHeaders)
                $Content->writeHeaders($Request, $Writer);

        return $Writer;
    }

    /**
     * Add HTML Container Content
     * @param IRenderHTML $Content
     * @return String|void always returns void
     */
    function addContent(IRenderHTML $Content) {
        $this->mContent[] = $Content;
    }

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