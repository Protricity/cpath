<?php
namespace CPath\Handlers\Themes;

use CPath\Base;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Helpers\Describable;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;


class CPathDefaultTheme implements ITheme {

    private $mIsHeader = false, $mIsException = false;

    protected function __construct() {
    }

    /**
     * Set up a view according to this theme
     * @param IView $View
     * @return mixed
     */
    function setupView(IView $View)
    {
        $basePath = Base::getClassPublicPath(__CLASS__, false);
        $View->addHeadStyleSheet($basePath . 'assets/cpathdefaulttheme.css');
    }

    /**
     * Render the start of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @param IDescribable|String|Null $Description optional fragment header text or description
     * @return void
     */
    function renderFragmentStart(IRequest $Request, $Description=null)
    {
        $errClass = $this->mIsException ? ' error' : '';
        echo RI::ni(), "<div class='fragment{$errClass}'>";
        echo RI::ai(1);
        if($Description) {
            echo RI::ni(), "<div class='fragment-title'>", Describable::get($Description)->getTitle(), "</div>";
        }
        echo RI::ni(), "<div>";
    }

    /**
     * Render the end of a fragment.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderFragmentEnd(IRequest $Request)
    {
        echo RI::ai(-1);
        echo RI::ni(1), "</div>";
        echo RI::ni(), "</div>";
    }

    /**
     * Render the start of a table.
     * @param IRequest $Request the IRequest instance for this render
     * @param String|NULL $captionText text that should appear in the table caption
     * @return void
     */
    function renderTableStart(IRequest $Request, $captionText = NULL)
    {
        $errClass = $this->mIsException ? ' error' : '';
        echo RI::ni(), "<table class='table{$errClass}'>";
        if($captionText)
            echo RI::ni(1), "<caption><em>{$captionText}</em></caption>";
        RI::ai(1);
    }

    /**
     * Render the start of a table row.
     * @param IRequest $Request the IRequest instance for this render
     * @param bool $isHeader set true if this row is a <th>
     * @return void
     */
    function renderTableRowStart(IRequest $Request, $isHeader=false)
    {
        $this->mIsHeader = $isHeader;

        echo RI::ni(), "<tr";

        if($isHeader)
            echo " class='table-header'";

        echo ">";

        RI::ai(1);
    }

    /**
     * Render the start of a table data element.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $span set span attribute
     * @return void
     */
    function renderTableDataStart(IRequest $Request, $span=0)
    {
        echo RI::ni();
        if($this->mIsHeader)
            echo '<th';
        else
            echo '<td';

        if($span)
            echo " rowspan='{$span}'";

        echo '>';

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

        if($this->mIsHeader)
            echo "</th>";
        else
            echo "</td>";
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
        RI::ai(-1);
        echo RI::ni(), "</table>";
    }

    /**
     * Render the start of an html <body>.
     * @param IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderBodyStart(IRequest $Request)
    {
        $errClass = $this->mIsException ? ' error' : '';
        echo RI::ni(), "<body>";
        echo RI::ni(1), "<div class='page{$errClass}'>";
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
     * @param String|Null $className optional class name for this section
     * @return void
     */
    function renderSectionStart(IRequest $Request, $className = NULL)
    {
        echo RI::ni();

        echo '<div';

        if($this->mIsException)
            $className .= $className ? ' error' : 'error';

        if($className)
            echo " class='", $className, "'";

        echo ">";

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
}

