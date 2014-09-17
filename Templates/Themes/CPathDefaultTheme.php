<?php
namespace CPath\Templates\Themes;

use CPath\Base;
use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Framework\PDO\Query\PDOSelect;
use CPath\Framework\PDO\Response\PDOSearchResponse;
use CPath\Render\HTML\Attribute\Attr;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Framework\Render\Header\ISupportHeaders;
use CPath\Render\HTML\Theme\Interfaces\IBrowseTheme;
use CPath\Render\HTML\Theme\IFragmentTheme;
use CPath\Render\HTML\Theme\Interfaces\IPageTheme;
use CPath\Render\HTML\Theme\Interfaces\ISearchTheme;
use CPath\Framework\Render\Util\RenderIndents as RI;
use CPath\Request\IRequest;
use CPath\Framework\Render\Fragment\Table\ModelResultsTableFragment;
use CPath\Framework\Render\Fragment\Table\ModelTableFragment;
use CPath\Framework\View\IContainerDEL;
use CPath\Render\HTML\Theme\ITableTheme;
use CPath\Framework\Render\Theme\Interfaces\ITheme;


class CPathDefaultTheme implements ITableTheme, IFragmentTheme, IPageTheme, ISupportHeaders {

    private $mRowBody = null, $mIsException = false, $mLastDataElm = null;

    public function __construct() {
    }

    /**
     * Write all support headers used by this IView instance
     * @param IRequest $Request the IRequest instance for this render
     * @param IHeaderWriter $Head the writer instance to use
     * @return String|void always returns void
     */
    function writeHeaders(IRequest $Request, IHeaderWriter $Head) {
        $Head->writeStyleSheet(__NAMESPACE__ . '\assets\cpathdefaulttheme.css', true);
        $Head->writeScript(__NAMESPACE__ . '\assets\cpathdefaulttheme.js', true);

        $this->mMTLF->writeHeaders($Request, $Head);
    }

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param IDescribable|String|Null $Description optional fragment header text or description
     * @param IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderFragmentStart(IRequest $Request, $Description=null, IAttributes $Attr=null) {
        $Attr = Attr::fromClass($Attr);

        $Attr->addClass('fragment');
        if($this->mIsException)
            $Attr->addClass('error');

        echo RI::ni(), "<div", $Attr->renderHTML($Request, $Attr), ">";
        RI::ai(1);
        if($Description) {
            echo RI::ni(), "<h4 class='fragment-title'>", Describable::get($Description)->getTitle(), "</h4>";
        }
        echo RI::ni(), "<div class='fragment-content'>";
        RI::ai(1);
    }

    /**
     * Render the end of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderFragmentEnd(IRequest $Request)
    {
        RI::ai(-2);
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
        $Attr = Attr::fromClass($Attr);

        $Attr->addClass('table');

        if($this->mIsException)
            $Attr->addClass('error');

        echo RI::ni(), "<table", $Attr->renderHTML($Request, $Attr), ">";
        if($captionText)
            echo RI::ni(1), "<caption><em>{$captionText}</em></caption>";
        RI::ai(1);
    }


    /**
     * Render the start of a table row.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $flags ::FLAG_ROW_IS_HEADER, ::FLAG_ROW_IS_FOOTER, FLAG_ROW_FIRST_DATA_IS_LABEL
     * @param \CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableRowStart(IRequest $Request, $flags=0, IAttributes $Attr=null) {
        $Attr = Attr::fromClass($Attr);

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

        echo RI::ni(), "<tr", $Attr->renderHTML($Request, $Attr), ">";
        RI::ai(1);
    }

    /**
     * Render the start of a table data element.
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @param int $span set span attribute
     * @param int $flags ::FLAG_DATA_IS_LABEL
     * @param \CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderTableDataStart(IRequest $Request, $span=0, $flags=0, IAttributes $Attr=null) {
        $Attr = Attr::fromClass($Attr);

        if($span)
            $Attr->add('colspan', $span);

        echo RI::ni();
        if($flags & ITableTheme::CHECK_FLAG_DATA_IS_LABEL)
            $this->mLastDataElm = 'th';
        else
            $this->mLastDataElm = 'td';
        echo '<', $this->mLastDataElm, $Attr->renderHTML($Request, $Attr), ">";

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
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
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
     * @param String|\CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content.
     * Note: If a string is passed instead of IAttributes, it is added as a class to a new instance of IAttributes
     * @return void
     */
    function renderBodyStart(IRequest $Request, IAttributes $Attr=NULL) {
        $Attr = Attr::fromClass($Attr);

        $Attr->addClass('page');

        if($this->mIsException)
            $Attr->addClass('error');

        echo RI::ni(), "<body class='narrow'>";
        echo RI::ni(1), "<div", $Attr->renderHTML($Request, $Attr), ">";
        RI::ai(2);
    }

    /**
     * Render the end of an html <body>.
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
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
     * @param \CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderSectionStart(IRequest $Request, IAttributes $Attr=NULL) {
        $Attr = Attr::fromClass($Attr);

        echo RI::ni();

        if($this->mIsException)
            $Attr->addClass('error');

        echo '<div', $Attr->renderHTML($Request, $Attr), '>';

        RI::ai(1);
    }

    /**
     * Render the end of an html body section.
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @param String|Null $className optional class name for this section
     * @return void
     */
    function renderSectionEnd(IRequest $Request, $className = NULL)
    {
        RI::ai(-1);
        echo RI::ni(), '</div>';
    }

    // Static

    /**
     * @return CPathDefaultTheme
     */
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
     * @param \CPath\Render\Attribute\\CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderBrowseContent(IRequest $Request, PDOSelect $Query, IAttributes $Attr = NULL) {
        foreach($Query as $data) {
            $MF = new ModelTableFragment($data, $this);
            $MF->getRenderer($Request);
        }
    }

    /**
     * Render the end of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param PDOSearchResponse $Response the PDOSearchResponse instance for this query
     * @param \CPath\Render\HTML\Attribute\IAttributes|NULL $Attr optional attributes to add to the content
     * @return void
     */
    function renderSearchContent(IRequest $Request, PDOSearchResponse $Response, IAttributes $Attr = NULL) {
        $this->mMTLF->renderHTML($Request, $Attr, $Response);
    }

}

