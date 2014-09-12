<?php
namespace CPath\Framework\Render\Layout\Common;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Render\HTML\Attribute\IAttributes;
use CPath\Render\HTML\IRenderHTML;
use CPath\Request\IRequest;
use CPath\Render\HTML\Page\ContentPage;
use CPath\Framework\Render\Header\IHeaderWriter;
use CPath\Render\HTML\Layout\ContentLayout;
use CPath\Framework\Render\Theme\Interfaces\ITheme;
use CPath\Framework\Render\Util\RenderIndents as RI;

class ObjectView extends ContentPage implements IDescribable {
    const FIELD_TITLE = 'title';
    private $mTarget;
    private $mDescription;

    /**
     * Construct an Object-based view
     * @param mixed $Target
     * @param \CPath\Framework\Render\Theme\Interfaces\ITheme $Theme
     */
    public function __construct($Target, ITheme $Theme=null) {
        $this->mTarget = $Target;
        $this->mDescription = Describable::get($Target);

        parent::__construct($Theme);
    }

    function getTarget() {
        return $this->mTarget;
    }

    /**
     * Render the html body for this view
     * @param IRequest $Request the IRequest instance for this render
     * @param IAttributes $Attr optional attributes for the input field
     * @return void
     */
    protected function renderHTMLBody(IRequest $Request, IAttributes $Attr = null)
    {
        if($this->mTarget instanceof IRenderHTML) {
            $this->mTarget->renderHTML($Request);
        } else {
            echo RI::ni(), $this->mDescription->getDescription();
        }
    }

    /**
     * Get a simple public-visible title of this object as it would be displayed in a header (i.e. "Mr. Root")
     * @return String title for this Object
     */
    function getTitle() {
        return $this->mDescription->getTitle();
    }

    /**
     * Get a simple public-visible description of this object as it would appear in a paragraph (i.e. "User account 'root' with ID 1234")
     * @return String simple description for this Object
     */
    function getDescription() {
        return $this->mDescription->getDescription();
    }

    /**
     * Get a simple world-visible description of this object as it would be used when cast to a String (i.e. "root", 1234)
     * Note: This method typically contains "return $this->getTitle();"
     * @return String simple description for this Object
     */
    function __toString() {
        return $this->mDescription->getTitle();
    }

}
