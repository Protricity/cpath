<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task;

interface ITaskCollection extends \IteratorAggregate {

    /**
     * Add an ITask to the collection
     * @param ITask $Task
     * @return ITaskCollection return self
     */
    function add(ITask $Task);

    /**
     * Return a list of tasks as filtered by filter* commands
     * @param boolean $reset if true, the filters will be reset
     * @return ITask[]
     */
    function getTasks($reset=true);

    /**
     * Filter the task list by flags using AND logic
     * @param int $flags - flags to filter by
     * @return ITaskCollection return self
     */
    function byFlags($flags);

    /**
     * Filter the task list by flags using AND logic
     * @param int $flags - flags to filter by
     * @return ITaskCollection return self
     */
    function byAnyFlag($flags);

    /**
     * Filter the task list by class name
     * @param String $className - class name to filter by
     * @return ITaskCollection return self
     */
    function byClass($className);

    /**
     * Filter the task list by a callback
     * @callback bool function(ITask $Task)
     * @param Callable|\Closure $callback - callback to filter by. Return === true to keep a task in the collection
     * @return ITaskCollection return self
     */
    function byCallback($callback);
}
