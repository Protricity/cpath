<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Actions;

class ActionManager implements IActionManager {

    /** @var IActionable[] */
    private $mActions = array();

    public function __construct() {

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