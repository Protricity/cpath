<?php
namespace CPath\Handlers;

use CPath\Config;
use CPath\Handlers\Interfaces\IView;
use CPath\Handlers\Themes\Interfaces\ITheme;
use CPath\Describable\Describable;
use CPath\Interfaces\IRequest;
use CPath\Misc\RenderIndents as RI;

abstract class ObjectView extends View{
    const FIELD_TITLE = 'title';
    private $mTarget;

    /**
     * Construct an Object-based view
     * @param mixed $Target
     * @param ITheme $Theme
     */
    public function __construct($Target, ITheme $Theme) {
        $this->mTarget = $Target;
        parent::__construct($Theme);
    }

    /**
     * Add additional <head> element fields for this View
     * @param IRequest $Request
     * @return void
     */
    abstract protected function addHeadFields(IRequest $Request);

    /**
     * Set up <head> element fields for this View
     * @param IRequest $Request
     */
    final protected function setupHeadFields(IRequest $Request) {
        $this->addHeadHTML("<title>" . Describable::get($this->mTarget)->getTitle() . "</title>", self::FIELD_TITLE);
        $this->addHeadFields($Request);
    }

    function getTarget() {
        return $this->mTarget;
    }
}
