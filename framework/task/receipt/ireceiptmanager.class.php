<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task\Receipt;

use CPath\Framework\Task\ITask;

interface IReceiptManager {

    /**
     * Create a new receipt for this task
     * @param ITask $Task
     * @return IReceipt
     */
    function createReceipt(ITask $Task);
}
