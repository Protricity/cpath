<?php
namespace CPath\Framework\Render\Layout\Common;

use CPath\Base;
use CPath\Framework\Render\Fragment\Common\HTMLFragment;
use CPath\Framework\Render\HTML\IRenderHTML;
use CPath\Framework\Render\Header\Interfaces\IHeaderWriter;
use CPath\Framework\Render\Header\Interfaces\ISupportHeaders;
use CPath\Framework\Render\Layout\AbstractLayout;
use CPath\Framework\Render\Theme\Interfaces\ITheme;

class SimpleLayout extends AbstractLayout implements ISupportHeaders {

    const SECTION_HEADER = 'header';
    const SECTION_BODY = 'body';
    const SECTION_FOOTER = 'footer';

    public function __construct(ITheme $Theme=null) {
        parent::__construct($Theme);
        $this->addSection(static::SECTION_HEADER);
        $this->addSection(static::SECTION_BODY);
        $this->addSection(static::SECTION_FOOTER);
    }

    public function addBodyFragment(IRenderHTML $Fragment, $before=false) {
        $this->addFragment($Fragment, static::SECTION_BODY, $before);
    }

    public function addHeaderFragment(IRenderHTML $Fragment, $before=false) {
        $this->addFragment($Fragment, static::SECTION_HEADER, $before);
    }

    public function addFooterFragment(IRenderHTML $Fragment, $before=false) {
        $this->addFragment($Fragment, static::SECTION_FOOTER, $before);
    }

    /**
     * Write all support headers used by this IView instance
     * @param \CPath\Framework\Render\Header\Interfaces\IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IHeaderWriter $Head)
    {
        $basePath = Base::getClassPath(__CLASS__);
        $Head->writeStyleSheet($basePath . 'assets/simplelayout.css');
    }
}

