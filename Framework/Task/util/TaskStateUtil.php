<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task;

use CPath\Framework\Task\Exceptions\InvalidTaskStateException;
use CPath\Framework\Task\Parameter\ITaskParameter;

class TaskStateUtil implements ITaskUtil{
    /** @var ITask  */
    private $mTask;

    function __construct(ITask $Task) {
        $this->mTask = $Task;
    }

    /**
     * Process the task state.
     * Note: if the flag for TASK_ACTIVE is not set, the task is generally seen as inactive or unavailable.
     * @param int $e existing task flags
     * @return int return new (or modified) flags
     * @throws InvalidTaskStateException
     */
    function processTaskState($e) {
        $flags = $this->process(ITask::EVENT_STATUS);
        switch(true) {
            default:
            case $e & ITask::EVENT_STATUS:
                return $flags;
                break;

            case $e & ITask::EVENT_START:
                if($flags & ITask::STATUS_ACTIVE ? false : true)
                    throw new InvalidTaskStateException("Inactive task was started");

                if($flags & ITask::STATUS_EXPIRED)
                    throw new InvalidTaskStateException("Expired task was started");

                if($flags & ITask::STATUS_COMPLETE)
                    throw new InvalidTaskStateException("Complete task was started");

                if($flags & ITask::STATUS_PENDING)
                    throw new InvalidTaskStateException("Pending task was started");

                $flags2 = $this->process($e);
                if($flags2 & (ITask::STATUS_PENDING | ITask::STATUS_COMPLETE) ? false : true)
                    throw new InvalidTaskStateException("Started task is not pending or complete");

                return $flags2;

            case $e & ITask::EVENT_PAUSE:

                if($flags & ITask::STATUS_PENDING ? false : true)
                    throw new InvalidTaskStateException("Pending task was paused");

                if($flags & ITask::STATUS_COMPLETE)
                    throw new InvalidTaskStateException("Complete task was paused");

                $flags2 = $this->process($e);

                if($flags2 & (ITask::STATUS_PENDING | ITask::STATUS_ABORTED))
                    throw new InvalidTaskStateException("Paused task is not pending or aborted");

                return $flags2;

            case $e & ITask::EVENT_TIMEOUT:
                $flags2 = $this->process($e);
                if($flags2 & ITask::STATUS_ABORTED ? false : true)
                    throw new InvalidTaskStateException("Task that timed out was not aborted");
                return $flags2;

            case $e & ITask::EVENT_CREATE:
                $flags2 = $this->process($e);
                if($flags2 & ITask::STATUS_ACTIVE ? false : true)
                    throw new InvalidTaskStateException("Inactive task was created");

                return $flags2;
        }
    }

    private function process($eventFlags) {
        try {
            return $this
                ->mTask
                ->processTaskState($eventFlags);
        } catch (\Exception $ex) {
            return ITask::STATUS_ERROR;
        }
    }

    /**
     * @return ITaskParameter[]
     */
    function getParameters() {
        return $this->mTask->getParameters();
    }
}