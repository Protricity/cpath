<?php
namespace CPath\Handlers\Themes;

use CPath\Base;
use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Response\PDOSearchResponse;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Handlers\Fragments\ModelResultsTableFragment;
use CPath\Handlers\Fragments\ModelTableFragment;
use CPath\Framework\Render\Interfaces\IAttributes;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Framework\Render\Util\Attr;
use CPath\Framework\Render\Util\RenderIndents as RI;


class CPathDefaultTheme implements ITheme {

    private $mRowBody = null, $mIsException = false, $mLastDataElm = null;

    /** @var  ModelResultsTableFragment */
    private $mMTLF;

    protected function __construct() {
        $this->mMTLF = new ModelResultsTableFragment($this);
    }

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View) {
        $basePath = Base::getClassPublicPath(__CLASS__, false);
        $View->addHeadStyleSheet($basePath . 'assets/cpathdefaulttheme.css', true);
        $View->addHeadScript($basePath . 'assets/cpathdefaulttheme.js', true);

        $this->mMTLF->addHeadElementsToView($View);
    }

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param IDescribable|String|Null $Description optional fragment header text or description
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderFragmentStart(IRequest $Request, $Description=null, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);

        $Attr->addClass('fragment');
        if($this->mIsException)
            $Attr->addClass('error');

        echo RI::ni(), "<div", $Attr->render($Request), ">";
        echo RI::ai(1);
        if($Description) {
            echo RI::ni(), "<h4 class='fragment-title'>", Describable::get($Description)->getTitle(), "</h4>";
        }
        echo RI::ni(), "<div class='fragment-content'>";
        echo RI::ai(1);
    }

    /**
     * Render the end of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderFragmentEnd(IRequest $Request)
    {
        echo RI::ai(-2);
        echo RI::ni(1), "</div>";
        echo RI::ni(), "</div>";
    }

    /**
     * Render the start of a table.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $captionText text that should appear in the table caption
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableStart(IRequest $Request, $captionText = NULL, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);

        $Attr->addClass('table');

        if($this->mIsException)
            $Attr->addClass('error');

        echo RI::ni(), "<table", $Attr->render($Request), ">";
        if($captionText)
            echo RI::ni(1), "<caption><em>{$captionText}</em></caption>";
        RI::ai(1);
    }


    /**
     * Render the start of a table row.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $flags ::FLAG_ROW_IS_HEADER, ::FLAG_ROW_IS_FOOTER, FLAG_ROW_FIRST_DATA_IS_LABEL
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableRowStart(IRequest $Request, $flags=0, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);

        if($flags & ITableTheme::FLAG_ROW_IS_HEADER)
                $body = 'thead';
        elseif($flags & ITableTheme::FLAG_ROW_IS_FOOTER)
            $body = 'tfoot';
        else
            $body = 'tbody';

        if($this->mRowBody != $body) {
            if($this->mRowBody) {
                RI::ai(-1);
                echo RI::ni(), "</", $this->mRowBody, ">";
            }
            echo RI::ni(), "<", $body, ">";
            RI::ai(1);

        }
        $this->mRowBody = $body;

        echo RI::ni(), "<tr", $Attr->render($Request), ">";
        RI::ai(1);
    }

    /**
     * Render the start of a table data element.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $span set span attribute
     * @param int $flags ::FLAG_DATA_IS_LABEL
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableDataStart(IRequest $Request, $span=0, $flags=0, IAttributes $Attr=null) {
        $Attr = Attr::get($Attr);

        if($span)
            $Attr->add('colspan', $span);

        echo RI::ni();
        if($flags & ITableTheme::CHECK_FLAG_DATA_IS_LABEL)
            $this->mLastDataElm = 'th';
        else
            $this->mLastDataElm = 'td';
        echo '<', $this->mLastDataElm, $Attr->render($Request), ">";

        RI::ai(1);
    }

    /**
     * Render the start of a table data element.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableDataEnd(IRequest $Request)
    {
        RI::ai(-1);
        echo RI::ni();

        echo '</' . $this->mLastDataElm . '>';
    }

    /**
     * Render the end of a table row.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderTableRowEnd(IRequest $Request)
    {
        RI::ai(-1);
        echo RI::ni(), "</tr>";
    }

    /**
     * Render the end of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $footerText text that should appear in the footer
     * @return void
     */
    function renderTableEnd(IRequest $Request, $footerText = NULL)
    {
        if($this->mRowBody) {
            RI::ai(-1);
            echo RI::ni(), "</", $this->mRowBody, ">";
        }
        $this->mRowBody = null;

        RI::ai(-1);
        echo RI::ni(), "</table>";
    }

    /**
     * Render the start of an html <body>.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|IAttributes|NULL $Attr optional attributes to add to the content.
     * Note: If a string is passed instead of IAttributes, it is added as a class to a new instance of IAttributes
     * @return void
     */
    function renderBodyStart(IRequest $Request, IAttributes $Attr=NULL) {
        $Attr = Attr::get($Attr);

        $Attr->addClass('page');

        if($this->mIsException)
            $Attr->addClass('error');

        echo RI::ni(), "<body class='narrow'>";
        echo RI::ni(1), "<div", $Attr->render($Request), ">";
        RI::ai(2);
    }

    /**
     * Render the end of an html <body>.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderBodyEnd(IRequest $Request)
    {
        RI::ai(-2);
        echo RI::ni(1), "</div>";
        echo RI::ni(), "</body>";
    }

    /**
     * Render the start of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderSectionStart(IRequest $Request, IAttributes $Attr=NULL) {
        $Attr = Attr::get($Attr);

        echo RI::ni();

        if($this->mIsException)
            $Attr->addClass('error');

        echo '<div', $Attr->render($Request), '>';

        RI::ai(1);
    }

    /**
     * Render the end of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|Null $className optional class name for this section
     * @return void
     */
    function renderSectionEnd(IRequest $Request, $className = NULL)
    {
        RI::ai(-1);
        echo RI::ni(), '</div>';
    }

    // Static

    static function get() { return new static; }
    static function getError() {
        /** @var CPathDefaultTheme $inst */
        $inst = new static;
        $inst->mIsException = true;
        return $inst;
    }

    /**
     * Render the results of a query.
     * @param IRequest $Request the IRequest instance for this render
     * @param PDOSelect $Query query instance to render (not yet executed)
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderBrowseContent(IRequest $Request, PDOSelect $Query, IAttributes $Attr = NULL) {
        foreach($Query as $data) {
            $MF = new ModelTableFragment($data, $this);
            $MF->render($Request);
        }
    }

    /**
     * Render the end of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param PDOSearchResponse $Response the PDOSearchResponse instance for this query
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderSearchContent(IRequest $Request, PDOSearchResponse $Response, IAttributes $Attr = NULL) {
        $this->mMTLF->render($Request, $Response, $Attr);
    }
}

