<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Tasks;

use CPath\Base;
use CPath\Describable\IDescribable;
use CPath\Describable\IDescribableAggregate;
use CPath\Framework\API\Fragments\SimpleFormFragment;
use CPath\Framework\API\Interfaces\IAPI;
use CPath\Request\IRequest;
use CPath\Framework\Task\ITask;
use CPath\Framework\View\IContainerDEL;
use CPath\Framework\Render\Theme\Interfaces\ITheme;
use CPath\Interfaces\IViewConfig;

abstract class APITask implements ITask, IViewConfig, IDescribableAggregate {
    private $mAPI=null, $mUtil=null;
    function __construct() {
    }

    /**
     * @return IAPI
     */
    abstract protected function loadAPI();

    private function getAPI() {
        return $this->mAPI ?: $this->mAPI = $this->loadAPI();
    }

    private function getUtil(ITheme $Theme=null) {
        return $this->mUtil ?: $this->mUtil = new SimpleFormFragment($Theme);
    }

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IContainerDEL $View
     */
    function addHeadElementsToView(IContainerDEL $View) {
        //parent::addHeadElementsToView($View);

        $basePath = Base::getClassPath($this, true);
        $Head->writeStyleSheet($basePath . 'assets/apiactions.css', true);
        $Head->writeScript($basePath . 'assets/apiactions.js', true);

        $this->getUtil()->addHeadElementsToView($View);
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return $this->getAPI();
    }

    /**
     * Filter this action according to the present circumstances
     * @param IRequest $Request
     * @return bool true if this action should execute. Return not true if this action does not apply
     */
    function execute(IRequest $Request, $args) {
        return $this->getAPI()->execute($Request);
    }

    /**
     * Render the fragment content
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render
     * @return void
     */
    function renderFragmentContent(IRequest $Request) {
        $this->getUtil()->renderDestination($Request, 'api-action'); // TODO: serialize?
    }

}