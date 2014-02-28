<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task;

use CPath\Framework\Data\Collection\ICollection;

interface ITaskCollection extends ICollection {

    /**
     * Add an ITask to the collection
     * @param ITask $Task
     * @return ITaskCollection return self
     */
    function add(ITask $Task);

    /**
     * Filter the task list by flags using AND logic
     * @param int $flags - flags to filter by
     * @return ITaskCollection return self
     */
    function whereFlags($flags);

    /**
     * Filter the task list by flags using OR logic
     * @param int $flags - flags to filter by
     * @return ITaskCollection return self
     */
    function whereAnyFlag($flags);

}
