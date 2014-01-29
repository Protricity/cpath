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
    const STATUS_ACTIVE         = 0x1;      // Task is in active state
    const STATUS_ERROR          = 0x2;      // Task is in error state
    const STATUS_EXPIRED        = 0x4;      // Task is in expired state
    const STATUS_PRIORITY       = 0x8;      // High priority task

    // State flags
    const STATE_PENDING         = 0x10;     // Task is pending or queued and will complete later
    const STATE_COMPLETE        = 0x20;     // Task is complete
    const STATE_ABORTED         = 0x40;     // Task is aborted

    // Event flags
    const EVENT_STATUS          = 0x1;      // Task status has been requested. No processing should occur

    const EVENT_CREATE          = 0x10;     // Task has been created
    const EVENT_START           = 0x20;     // Task has been started
    const EVENT_PAUSE           = 0x40;     // Task has been paused

    const EVENT_TIMEOUT         = 0x100;    // Task is timing out and about to abort

    /**
     * Process the task state.
     * Note: if the flag for TASK_ACTIVE is not set, the task is generally seen as inactive or unavailable.
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     */
    function processTaskState($eventFlags);
}
