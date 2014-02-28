<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task;

use CPath\Framework\Data\Collection\AbstractCollection;
use CPath\Framework\Data\Collection\Predicate\IPredicate;

class TaskList extends AbstractCollection {

    /**
     * Add an ITask to the collection
     * @param ITask $Task
     * @return TaskList return self
     */
    function add(ITask $Task) {
        return $this->addItem($Task);
    }

    /**
     * Filter the task list by flags using AND logic
     * @param int $flags - flags to filter by
     * @return TaskList return self
     */
    function byFlags($flags) {
        return $this->where(new TaskListHasAllFlagsPredicate($flags));
    }

    /**
     * Filter the task list by flags using OR logic
     * @param int $flags - flags to filter by
     * @return TaskList return self
     */
    function byAnyFlag($flags) {
        return $this->where(new TaskListHasAnyFlagPredicate($flags));
    }

    /**
     * Filter the item collection by an IPredicate
     * @param \CPath\Framework\Data\Collection\Predicates\IPredicate $Where
     * @return TaskList return self
     */
    function where(IPredicate $Where) { return parent::where($Where); }
}

class TaskListHasAnyFlagPredicate implements IPredicate {
    private $mFlags;
    function __construct($flags) { $this->mFlags = $flags; }

    function onPredicate($Object) {
        if(!$Object instanceof ITask)
            return false;

        return $Object->processTaskState(ITask::EVENT_STATUS) | $this->mFlags ? true : false;
    }
}


class TaskListHasAllFlagsPredicate implements IPredicate {
    private $mFlags;
    function __construct($flags) { $this->mFlags = $flags; }

    function onPredicate($Object) {
        if(!$Object instanceof ITask)
            return false;

        return $Object->processTaskState(ITask::EVENT_STATUS) & $this->mFlags ? true : false;
    }
}
