<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/1/14
 * Time: 9:38 PM
 */
namespace CPath\Handlers\HTML\Navigation;

use CPath\Describable\Describable;
use CPath\Describable\IDescribable;
use CPath\Request\IRequestHandlerAggregate;

class RouteLink implements IDescribable {

    private $mURL;
    private $mDescription = null;
    private $mDestination;

    public function __construct(IRequestHandlerAggregate $Destination, $url, $matched) {
        $this->mURL = $url;
        $this->mDestination = $Destination;
    }

    /**
     * Get a simple public-visible title of this object as it would be displayed in a header (i.e. "Mr. Root")
     * @return String title for this Object
     */
    function getTitle() {
        $Desc = $this->mDescription ?: $this->mDescription = Describable::get($this->mDestination);
        return $Desc->getTitle();
    }

    /**
     * Get a simple public-visible description of this object as it would appear in a paragraph (i.e. "User account 'root' with ID 1234")
     * @return String simple description for this Object
     */
    function getDescription() {
        $Desc = $this->mDescription ?: $this->mDescription = Describable::get($this->mDestination);
        return $Desc->getDescription();
    }

    /**
     * Generate a hyperlink for this entry
     * @return string
     */
    function getHyperlink() {
        $Desc = $this->mDescription ?: $this->mDescription = Describable::get($this->mDestination);
        $url = "<a href='" . $this->mURL . "'";
        if($Desc->getTitle())
            $url .= " title='" . $Desc->getTitle() . "'";
        $url .= ">" . $Desc->getDescription() . "</a>";
        return $url;
    }

    /**
     * Get a simple world-visible description of this object as it would be used when cast to a String (i.e. "root", 1234)
     * Note: This method typically contains "return $this->getTitle();"
     * @return String simple description for this Object
     */
    function __toString() {
        return $this->getHyperlink();
    }
}