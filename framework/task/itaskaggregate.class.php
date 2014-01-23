<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task;

interface ITaskAggregate {

    /**
     * Load all available tasks from this object into the task manager.
     * @param ITaskCollection $Tasks the collection to add to [$Tasks->add(new MyTask(...]
     * @return void
     */
    function loadTasks(ITaskCollection $Tasks);
}
