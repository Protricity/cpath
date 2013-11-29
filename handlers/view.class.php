<?php
namespace CPath\Handlers;

use CPath\Config;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Helpers\Describable;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class View implements IView {

    const FIELD_BASE = 'basePath';
    const FIELD_TITLE = 'title';
    const FIELD_SCRIPT_JQUERY = 'script_jQuery';

    const TAB = '    ';
    const TAB_START = 0;

    private $mHeadFields = array();
    private $mTarget, $mTheme, $mBasePath;

    public function __construct($Target, ITheme $Theme) {
        $this->mTarget = $Target;
        $this->mTheme = $Theme;
        $this->mBasePath = Config::getDomainPath();
        $this->setupHeadFields();
        if($Theme)
            $Theme->setupView($this);

        RI::si(static::TAB_START, static::TAB);
    }

    /**
     * Render this handler
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    final function render(IRequest $Request) {
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

    protected function setupHeadFields() {
        $this->mHeadFields[self::FIELD_TITLE] = "<title>" . Describable::get($this->mTarget)->getTitle() . "</title>";NULL;
        $this->mHeadFields[self::FIELD_BASE] = "<base href='{$this->mBasePath}' />";
        $this->mHeadFields[self::FIELD_SCRIPT_JQUERY] = "<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>";
    }

    function getTarget() {
        return $this->mTarget;
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
        $this->mHeadFields[self::FIELD_TITLE] = "<title>{$title}</title>";
    }

    function addHeadHTML($html, $key=null, $replace=false) {
        if(!$key) {
            $this->mHeadFields[] = $html;
            return;
        }
        if(!isset($this->mHeadFields[$key])) {
            if($replace)
                throw new \InvalidArgumentException("Key '{$key}' does not exist in header fields");
            $this->mHeadFields[$key] = $html;
        } else {
            if(!$replace)
                throw new \InvalidArgumentException("Key '{$key}' already exist in header fields. Use \$replace==true to overwrite");
            $this->mHeadFields[$key] = $html;
        }
    }

    function addHeadScript($src, $key=null, $replace=false) {
        $this->addHeadHTML("<script src='{$src}'></script>", $key, $replace);
    }

    function addHeadStyleSheet($href, $key=null, $replace=false) {
        $this->addHeadHTML("<link rel='stylesheet' href='{$href}' />", $key, $replace);
    }
}
