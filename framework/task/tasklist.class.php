<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task;

use CPath\Type\Collection\AbstractCollection;

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
        return $this->where(function(ITask $Task) use ($flags) {
            return $Task->processTaskState(ITask::EVENT_STATUS) & $flags ? true : false;
        });
    }

    /**
     * Filter the task list by flags using AND logic
     * @param int $flags - flags to filter by
     * @return TaskList return self
     */
    function byAnyFlag($flags) {
        return $this->where(function(ITask $Task) use ($flags) {
            return $Task->processTaskState(ITask::EVENT_STATUS) | $flags ? true : false;
        });
    }

    /**
     * Return a list of all items in the collection
     * @return ITask[]
     */
    function getAll() { return parent::getAll(); }

    /**
     * Return a list of all items in the collection
     * @return ITask[]
     */
    function getFiltered() { return parent::getFiltered(); }
}