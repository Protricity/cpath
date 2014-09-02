<?php
namespace CPath\Framework\Render\Layout;

use CPath\Framework\Render\Attribute\Attr;
use CPath\Framework\Render\Attribute\IAttributes;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Render\Header\Common\WriteOnceHeaderRenderer;
use CPath\Framework\Render\Header\Interfaces\ISupportHeaders;
use CPath\Framework\Render\Page\AbstractPage;
use CPath\Framework\Render\Theme\CPathDefaultTheme;
use CPath\Framework\Render\Theme\Interfaces\ITheme;

abstract class AbstractLayout extends AbstractPage {

    private $mTheme;
    private $mSections = array();

    public function __construct(ITheme $Theme=null) {
        $this->mTheme = $Theme ?: CPathDefaultTheme::get();
        parent::__construct();
    }

    /**
     * @return \CPath\Framework\Render\Theme\Interfaces\ITheme
     */
    function getTheme() {
        return $this->mTheme;
    }

    function addSection($name, $before=false) {
        if(isset($this->mSections[$name])) {
            throw new \Exception("Section already exists: " . $name);
        }
        if($before) {
            $this->mSections = array($name => array()) + $this->mSections;
        } else {
            $this->mSections[$name] = array();
        }
    }

    function addFragment(IRenderHTML $Fragment, $section, $before=false) {
        if(!isset($this->mSections[$section])) {
            throw new \Exception("Section not found: " . $section);
        }

        if($before) {
            array_unshift($this->mSections[$section], $Fragment);
        } else {
            $this->mSections[$section][] = $Fragment;
        }
    }

    /**
     * Render HTML header html
     * @param IRequest $Request
     */
    protected function renderHTMLHeaders(IRequest $Request) {

        parent::renderHTMLHeaders($Request);

        $Head = new WriteOnceHeaderRenderer();

//        $basePath = Base::getClassPath(__CLASS__);
//        $Head->writeStyleSheet($basePath . 'assets/pagelayout.css');

        foreach($this->mSections as $section) {
            foreach($section as $Fragment) {
                if($Fragment instanceof ISupportHeaders) {
                    $Fragment->writeHeaders($Head);
                }
            }
        }
    }

    /**
     * Render the html body
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    final protected function renderHTMLBody(IRequest $Request, IAttributes $Attr = null) {
        $Theme = $this->mTheme;
        $Theme->renderBodyStart($Request);

        foreach($this->mSections as $sectionName => $section) {
            foreach($section as $Fragment) {
                /** @var IRenderHTML $Fragment */

                $Theme->renderSectionStart($Request, Attr::get($sectionName));

                $Fragment->renderHTML($Request);

                $Theme->renderSectionEnd($Request);
            }
        }

        $Theme->renderBodyEnd($Request);
    }
}

