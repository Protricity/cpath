<?php
namespace CPath\Framework\View\Templates\Common;

use CPath\Config;
use CPath\Describable\Describable;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\View\Common\AbstractView;
use CPath\Framework\View\Theme\Interfaces\ITheme;

abstract class ObjectView extends AbstractView{
    const FIELD_TITLE = 'title';
    private $mTarget;

    /**
     * Construct an Object-based view
     * @param mixed $Target
     * @param \CPath\Framework\View\Theme\Interfaces\ITheme $Theme
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
