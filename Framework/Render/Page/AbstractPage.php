<?php
namespace CPath\Framework\Render\Page;

use CPath\Config;
use CPath\Describable\IDescribable;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Render\Header\Common\WriteOnceHeaderRenderer;
use CPath\Framework\Render\Header\Interfaces\ISupportHeaders;


abstract class AbstractPage implements IRenderHTML {
    const TAB = '    ';
    const TAB_START = 0;

    const RESPONSE_CODE = 200;
    const RESPONSE_MESSAGE = 'OK';
    const RESPONSE_MIMETYPE = 'text/html';

    //private $mHeadFields = array();
    //private $mTheme;

    public function __construct() {
        //$this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Render the html body for this view
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    abstract protected function renderHTMLBody(IRequest $Request, IAttributes $Attr = null);


//    /**
//     * Add a <base/> element with href=$path
//     * @param IRequest $Request
//     * @param String|null $path The specified base path or null for default path
//     */
//    final protected function renderHeaderBaseHTML(IRequest $Request, $path=null) {
//        if(!$path && $Request->getPath()) {
//            $path = $Request->getPath();
//            if($Request instanceof ModifiedRequestWrapper)
//                $path = $Request->getMatchedPath();
//        }
//        if($path) {
//            $basePath = rtrim(Config::getDomainPath(), '/') . rtrim($path, '/') . '/';
//            echo RI::ni(), "<base href='{$basePath}' />";
//        }
//
//        // TODO throw exception of two base elements are rendered at once
//    }

    protected function sendHeaders($message=NULL, $code=NULL, $mimeType=NULL) {
        if(!headers_sent()) {
            $message = $message ? preg_replace('/[^\w -]/', '', $message) : static::RESPONSE_MESSAGE;
            header("HTTP/1.1 " . ($code ?: static::RESPONSE_CODE) . " " . $message);
            if(static::RESPONSE_MIMETYPE)
                header("Content-Type: " . ($mimeType ?: static::RESPONSE_MIMETYPE));
        }
    }

    /**
     * Render the view body html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHTML(IRequest $Request, IAttributes $Attr = null)
    {
        RI::si(static::TAB_START, static::TAB);

        $this->sendHeaders();

        echo RI::ni(), '<html>';
        RI::ai(1);

            echo RI::ni(), '<head>';
            RI::ai(1);

                $this->renderHTMLHeaders($Request);

            RI::ai(-1);
            echo RI::ni(), '</head>';



            echo RI::ni(), '<body>';
            RI::ai(1);

                $this->renderHTMLBody($Request, $Attr);

            RI::ai(-1);
            echo RI::ni(), '</body>';

        RI::ai(-1);
        echo RI::ni(), '</html>';
    }

    /**
     * Render HTML header html
     * @param IRequest $Request
     */
    protected function renderHTMLHeaders(IRequest $Request) {

        if($this instanceof IDescribable) {
            $title = $this->getTitle();
            echo RI::ni(), "<title>", $title, "</title>";
        }

        if($this instanceof ISupportHeaders) {
            $Writer = new WriteOnceHeaderRenderer();
            $this->writeHeaders($Writer);
        }

        if($this->mTheme instanceof ISupportHeaders) {
            $Writer = new WriteOnceHeaderRenderer();
            $this->mTheme->writeHeaders($Writer);
        }
    }
}
