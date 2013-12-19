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

    protected function setupHeadFields() {
        $this->addHeadHTML("<title>" . Describable::get($this->mTarget)->getTitle() . "</title>", self::FIELD_TITLE);
    }

    function getTarget() {
        return $this->mTarget;
    }
}
