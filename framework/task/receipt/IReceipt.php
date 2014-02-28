<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task\Receipt;

use CPath\Framework\Task\ITask;
use CPath\Framework\Task\Parameter\ITaskParameter;

interface IReceipt {

    // Event flags
    const EVENT_STATUS          = 0x1;      // Receipt status has been requested. No processing should occur
    const EVENT_VALIDATE        = 0x10;     // Validate Receipt

    /**
     * Returns the task state as recorded
     * @return ITask return the task state
     */
    function getTask();

    /**
     * Process the task receipt state
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     * @throws ReceiptException
     */
    function processReceiptState($eventFlags);

    /**
     * @return ITaskParameter[]
     */
    function getParameters();
}
