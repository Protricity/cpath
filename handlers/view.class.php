<?php
namespace CPath\Handlers;

use CPath\Base;
use CPath\Config;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;
use CPath\Misc\RenderIndents as RI;

abstract class View implements IView, IViewConfig {
    const TAB = '    ';
    const TAB_START = 0;

    const RESPONSE_CODE = 200;
    const RESPONSE_MESSAGE = 'OK';
    const RESPONSE_MIMETYPE = 'text/html';

    private $mHeadFields = array();
    private $mTheme, $mBasePath;

    public function __construct(ITheme $Theme) {
        $this->mTheme = $Theme;
        $this->mBasePath = Config::getDomainPath();

        RI::si(null, static::TAB);
    }

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     * @return void
     */
    abstract protected function setupHeadFields(IRequest $Request);

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     */
    final protected function setupHead(IRequest $Request) {
        $basePath = Base::getClassPublicPath(__CLASS__, false);
        $this->addHeadHTML("<base href='{$this->mBasePath}' />", true);
        $this->addHeadScript('https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', true);
        $this->addHeadScript($basePath . 'assets/cpath.js', true);

        $this->setupHeadFields($Request);

        //$this->getActionManager()->addHeadElementsToView($this);
    }

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View) {
        foreach($this->mHeadFields as $key => $html)
            $View->addHeadHTML($html, $key, true);
    }

    protected function sendHeaders($message=NULL, $code=NULL, $mimeType=NULL) {
        if(!headers_sent()) {
            $message = preg_replace('/[^\w -]/', '', $message ?: static::RESPONSE_MESSAGE);
            header("HTTP/1.1 " . ($code ?: static::RESPONSE_CODE) . " " . $message);
            if(static::RESPONSE_MIMETYPE)
                header("Content-Type: " . ($mimeType ?: static::RESPONSE_MIMETYPE));
        }
    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final function render(IRequest $Request) {
        $this->setupHead($Request);
        $this->getTheme()->addHeadElementsToView($this);

        $this->sendHeaders();
        $this->renderHtmlTagStart();
        RI::ai(1);
        $this->renderHead($Request);
        $this->renderBody($Request);
        RI::ai(-1);
        $this->renderHtmlTagEnd();
    }

    function renderHead(IRequest $Request) {
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

    function getTheme() {
        return $this->mTheme;
    }

    function getBasePath($appendPath=NULL) {
        if($appendPath)
            return $this->mBasePath . '/' . $appendPath;
        return $this->mBasePath;
    }

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
        $this->addHeadHTML("<script src='{$src}'></script>", $replace);
    }

    function addHeadStyleSheet($href, $replace=false) {
        $this->addHeadHTML("<link rel='stylesheet' href='{$href}' />", $replace);
    }
}
