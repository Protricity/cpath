<?php
namespace CPath\Framework\View\Common;

use CPath\Config;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\IRender;
use CPath\Framework\Render\IRenderAggregate;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Framework\Render\Util\RenderMimeSwitchUtility;
use CPath\Framework\Request\Common\ModifiedRequestWrapper;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\IRenderContainer;
use CPath\Framework\View\IView;
use CPath\Framework\View\Theme\CPathDefaultTheme;
use CPath\Framework\View\Theme\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;

abstract class AbstractView implements IView, IRenderContainer { //}, IRenderAggregate {
    const TAB = '    ';
    const TAB_START = 0;

    const RESPONSE_CODE = 200;
    const RESPONSE_MESSAGE = 'OK';
    const RESPONSE_MIMETYPE = 'text/html';

    private $mHeadFields = array();
    private $mTheme;

    private $mRenders = array();

    public function __construct(ITheme $Theme=null) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     * @return void
     */
    abstract protected function setupHeadFields(IRequest $Request);

    /**
     * Render the html body
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    abstract protected function renderBody(IRequest $Request, IAttributes $Attr = null);

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     */
    final protected function setupHead(IRequest $Request) {

        //$basePath = Base::getClassPublicPath(__CLASS__);
        //$this->addHeadScript('https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', true);
        //$this->addHeadScript($basePath . 'assets/cpath.js', true);

        $this->setupHeadFields($Request);

        //$this->getActionManager()->addHeadElementsToView($this);
    }

    /**
     * Add a <base/> element with href=$path
     * @param IRequest $Request
     * @param String|null $path The specified base path or null for default path
     */
    final protected function addBaseElementToView(IRequest $Request, $path=null) {
        if(!$path && $Request->getPath()) {
            $path = $Request->getPath();
            if($Request instanceof ModifiedRequestWrapper)
                $path = $Request->getMatchedPath();
        }
        if($path) {
            $basePath = rtrim(Config::getDomainPath(), '/') . rtrim($path, '/') . '/';
            $this->addHeadHTML("<base href='{$basePath}' />", true);
        }
    }

//    /**
//     * Provide head elements to any IView
//     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
//     * @param IView $View
//     */
//    final function addHeadElementsToView(IView $View) {
//        foreach($this->mHeadFields as $key => $html)
//            $View->addHeadHTML($html, $key, true);
//    }

    protected function sendHeaders($message=NULL, $code=NULL, $mimeType=NULL) {
        if(!headers_sent()) {
            $message = preg_replace('/[^\w -]/', '', $message ?: static::RESPONSE_MESSAGE);
            header("HTTP/1.1 " . ($code ?: static::RESPONSE_CODE) . " " . $message);
            if(static::RESPONSE_MIMETYPE)
                header("Content-Type: " . ($mimeType ?: static::RESPONSE_MIMETYPE));
        }
    }


    /**
     * @param IRenderHTML $Renderer
     * @return mixed
     */
    function addRenderItem(IRenderHTML $Renderer)
    {
        $this->mRenders[] = $Renderer;
    }

//    /**
//     * Return an instance of IRender
//     * @param \CPath\Framework\Request\Interfaces\IRequest $Request
//     * @return IRender return the renderer instance
//     */
//    function getRenderer(IRequest $Request) {
//        return $this;
//    }

    /**
     * Render this request and sends headers as necessary
     * @param IRequest $Request the IRequest instance for this render
     * @return String|void always returns void
     */
    function render(IRequest $Request) {
        // Util allows selective rendering based on request mime type
//        $Util = new RenderMimeSwitchUtility($this);
//        $Util->render($Request);


        $this->setupHead($Request);
        $this->getTheme()->addHeadElementsToView($this);

        $this->sendHeaders();
        $this->renderHtmlTagStart();
        RI::ai(1);
        $this->renderHead($Request);
        todo


        //$this->renderBody($Request, $Attr);
        RI::ai(-1);
        $this->renderHtmlTagEnd();

    }

    /**
     * Render the view body html
     * @param IRequest $Request the IRequest instance for this render which contains the request and remaining args
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    function renderHtml(IRequest $Request, IAttributes $Attr = null)
    {
//        $this->setupHead($Request);
//        $this->getTheme()->addHeadElementsToView($this);
//
//        $this->sendHeaders();
//        $this->renderHtmlTagStart();
//        RI::ai(1);
//        $this->renderHead($Request);
//        todo
//
//
//        //$this->renderBody($Request, $Attr);
//        RI::ai(-1);
//        $this->renderHtmlTagEnd();
    }

    function renderHead() {
        echo RI::ni(), '<head>';
            RI::ai(1);
            foreach($this->mHeadFields as $html)
                if($html)
                    echo RI::ni(), $html;
            RI::ai(-1);
        echo RI::ni(), '</head>';
    }

    protected function renderHtmlTagStart() {
        echo '<html>';
    }

    protected function renderHtmlTagEnd() {
        echo RI::ni(), '</html>';
    }

    /**
     * @return \CPath\Framework\View\Theme\Interfaces\ITheme
     */
    function getTheme() {
        return $this->mTheme;
    }

//    function getBasePath($appendPath=NULL) {
//        if($appendPath)
//            return $this->mBasePath . '/' . $appendPath;
//        return $this->mBasePath;
//    }

    function setTitle($title) {
        $this->mHeadFields['title'] = "<title>{$title}</title>";
    }

    function addHeadHTML($html, $replace=false) {
        $key = crc32($html);
        if(!isset($this->mHeadFields[$key])) {
            //if($replace)
            //    throw new \InvalidArgumentException("Key '{$key}' does not exist in header fields");
            $this->mHeadFields[$key] = $html;
        } else {
            if(!$replace)
                throw new \InvalidArgumentException("Key '{$key}' already exist in header fields. Use \$replace==true to overwrite");
            $this->mHeadFields[$key] = $html;
        }
    }

    function addHeadScript($src, $replace=false) {
        $this->addHeadHTML("<script src='".$src."'></script>", $replace);
    }

    function addHeadStyleSheet($href, $replace=false) {
        $this->addHeadHTML("<link rel='stylesheet' href='".$href."' />", $replace);
    }
}
