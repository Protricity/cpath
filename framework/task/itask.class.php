<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task;

interface ITask {

    // Status

    const TASK_ACTIVE           = 0x1;    // Task is in active state
    const TASK_ERROR            = 0x2;    // Task is in error state
    const TASK_EXPIRED          = 0x4;    // Task is in expired state
    const TASK_PRIORITY         = 0x8;    // High priority task

    // States
    const TASK_STARTED          = 0x10;    // Task has started
    //const TASK_COMPLETE       = 0x30;    // Task is complete
    //const TASK_COMPLETE       = 0x70;    // Task is complete
    const TASK_COMPLETE         = 0xF0;    // Task is complete


    /**
     * Return the task status flags.
     * Note: if the flag for TASK_ACTIVE is not set, the task is generally seen as inactive or unavailable.
     * @return int
     */
    function getTaskFlags();
}
