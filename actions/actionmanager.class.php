<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Actions;

use CPath\Handlers\Interfaces\IView;
use CPath\Interfaces\IViewConfig;

class ActionManager implements IActionManager {

    /** @var IActionable[] */
    private $mActions = array();

    public function __construct() {

    }

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View) {
        foreach($this->mActions as $Action)
            if($Action instanceof IViewConfig)
                $Action->addHeadElementsToView($View);
    }

    /**
     * Add an action to the available list
     * @param IActionable $Action
     * @return mixed
     */
    function addAction(IActionable $Action) {
        $this->mActions[] = $Action;
    }

    /**
     * Return all available actions
     * @return IActionable[]
     */
    function getActions() {
        return $this->mActions;
    }

    function loadActionsFrom(IActionAggregate $Object) {
        $Object->loadActions($this);
    }
}