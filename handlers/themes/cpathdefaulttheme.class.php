<?php
namespace CPath\Handlers\Themes;

use CPath\Base;
use CPath\Handlers\Fragments\ModelTableFragment;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\ITableTheme;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Helpers\Describable;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;
use CPath\Model\DB\PDOSelect;


class CPathDefaultTheme implements ITheme {

    private $mRowFlags=0, $mRowBody = null, $mIsException = false;

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
        $View->addHeadScript($basePath . 'assets/cpathdefaulttheme.js');
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
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderTableStart(IRequest $Request, $captionText = NULL, $class=null, $attr=null)
    {
        if(is_array($attr))     $attr = implode(' ', $attr);
        if(is_array($class))    $class = implode(' ', $class);

        $class = 'table' . ($class ? ' ' : '') . $class;

        if($this->mIsException)
            $class .= ' error';

        echo RI::ni(), "<table", $attr ? ' '.$attr : '', " class='{$class}'", ">";
        if($captionText)
            echo RI::ni(1), "<caption><em>{$captionText}</em></caption>";
        RI::ai(1);
    }

    /**
     * Render the start of a table row.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $rowFlags ::FLAG_ROW_IS_HEADER, ::FLAG_ROW_IS_FOOTER
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderTableRowStart(IRequest $Request, $rowFlags=0, $class=null, $attr=null)
    {
        $this->mRowFlags = $rowFlags;

        if(is_array($attr))     $attr = implode(' ', $attr);
        if(is_array($class))    $class = implode(' ', $class);

        if($rowFlags & ITableTheme::FLAG_ROW_IS_HEADER)
                $body = 'thead';
        elseif($rowFlags & ITableTheme::FLAG_ROW_IS_FOOTER)
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

        echo RI::ni(), "<tr", $attr ? ' '.$attr : '', $class ? ' class=\''.$class.'\'' : '', ">";
        RI::ai(1);
    }

    /**
     * Render the start of a table data element.
     * @param IRequest $Request the IRequest instance for this render
     * @param int $span set span attribute
     * @param String|Array|NULL $class element classes
     * @param String|Array|NULL $attr element attributes
     * @return void
     */
    function renderTableDataStart(IRequest $Request, $span=0, $class=null, $attr=null)
    {
        if(is_array($attr))     $attr = implode(' ', $attr);
        if(is_array($class))    $class = implode(' ', $class);
        if($span)               $attr .= ($attr ? ' ' : '') . "colspan='{$span}'";

        echo RI::ni();
        if($this->mRowFlags && (ITableTheme::FLAG_ROW_IS_HEADER | ITableTheme::FLAG_ROW_IS_FOOTER))
            echo '<th';
        else
            echo '<td';

        echo $attr ? ' '.$attr : '', $class ? ' class=\''.$class.'\'' : '', ">";

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

        if($this->mRowFlags && (ITableTheme::FLAG_ROW_IS_HEADER | ITableTheme::FLAG_ROW_IS_FOOTER))
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
        $this->mRowFlags = 0;
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
     * @return void
     */
    function renderBodyStart(IRequest $Request)
    {
        $errClass = $this->mIsException ? ' error' : '';
        echo RI::ni(), "<body class='narrow'>";
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

    /**
     * Render the end of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param PDOSelect $Query query instance to render (not yet executed)
     * @param String|Null $className optional class name for this section
     * @return void
     */
    function renderBrowseContent(IRequest $Request, PDOSelect $Query, $className = NULL) {
        foreach($Query as $data) {
            $MF = new ModelTableFragment($data, $this);
            $MF->render($Request);
        }
    }

    /**
     * Render the end of an html body section.
     * @param IRequest $Request the IRequest instance for this render
     * @param PDOSelect $Query query instance to render (not yet executed)
     * @param String|Null $className optional class name for this section
     * @return void
     */
    function renderSearchContent(IRequest $Request, PDOSelect $Query, $className = NULL)
    {
        foreach($Query as $data) {
            $MF = new ModelTableFragment($data, $this);
            $MF->render($Request);
        }
    }
}

