<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;

use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Framework\Task\AbstractTask;
use CPath\Framework\Task\ITask;

class Task_Login extends AbstractTask {

    private $mUserTable;
    function __construct(PDOUserTable $UserTable) {
        $this->mUserTable = $UserTable;
    }

    /**
     * Process the task status.
     * Note: if ITask::STATUS_ACTIVE is not returned, the task will be flagged as inactive.
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     */
    protected function status($eventFlags) {
        return $this
            ->mUserTable
            ->loadBySession(false, false)
            ? 0
            : ITask::STATUS_ACTIVE;
    }

    /**
     * Start the task.
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     */
    protected function start($eventFlags) {
        // TODO: Implement start() method.
        throw new \InvalidArgumentException("Not implemented");
    }
}