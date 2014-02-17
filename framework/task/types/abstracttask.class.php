<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task\Types;

use CPath\Framework\Task\Exceptions\InvalidTaskStateException;
use CPath\Framework\Task\ITask;
use CPath\Framework\Task\Parameter\ITaskParameter;
use CPath\Framework\Data\Serialize\Interfaces\ISerializable;

abstract class AbstractTask implements ITask, ISerializable {

    /** @var ITaskParameter[] */
    private $mParameters = array();


    /**
     * Process the task status.
     * Note: if ITask::STATUS_ACTIVE is not returned, the task will be flagged as inactive.
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     * @throws InvalidTaskStateException
     */
    abstract protected function status($eventFlags);


    /**
     * Start the task.
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     * @throws InvalidTaskStateException
     */
    abstract protected function start($eventFlags);

    /**
     * Add a parameter to this task
     * @param ITaskParameter $Param
     * @return AbstractTask
     */
    protected function addParam(ITaskParameter $Param) {
        $this->mParameters[] = $Param;
        return $this;
    }

    /**
     * EXPORT Object to a simple data structure to be used in var_export($data, true)
     * @return mixed
     */
    function serialize() {
        $ser = array();
        foreach($this->mParameters as $Param)
            $ser[] = $Param;
        return $ser;
    }

    /**
     * Unserialize and instantiate an Object with the stored data
     * @param mixed $data the exported data
     * @return \CPath\Framework\Data\Serialize\Interfaces\ISerializable|AbstractTask
     */
    static function unserialize($data) {
        /** @var AbstractTask $Inst */
        $Inst = new static();
        foreach($Inst->getParameters() as $Param) {
            $name = $Param->getKey();
            if(empty($data[$name]))
                continue;

            $Param->setValue($data[$name]);
        }

        return $Inst;
    }

    /**
     * Process the task state.
     * Note: if the flag for TASK_ACTIVE is not set, the task is generally seen as inactive or unavailable.
     * @param int $e existing task flags
     * @return int return new (or modified) flags
     * @throws InvalidTaskStateException
     */
    function processTaskState($e) {
        switch(true) {
            default:
            case $e & ITask::EVENT_STATUS:
                return $this->status($e);

            case $e & ITask::EVENT_START:
                return $this->start($e);

            case $e & ITask::EVENT_PAUSE:
                return $this->pause($e);

            case $e & ITask::EVENT_TIMEOUT:
                return $this->timeout($e);

            case $e & ITask::EVENT_CREATE:
                return $this->timeout($e);
        }
    }

    /**
     * @return ITaskParameter[]
     */
    final function getParameters() {
        return $this->mParameters;
    }

    /**
     * Process an EVENT_CREATE event. The task has been created
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     * @throws InvalidTaskStateException
     */
    protected function create($eventFlags) { return ITask::STATUS_ACTIVE; }

    /**
     * Process an EVENT_PAUSE event. Pause the task
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     * @throws InvalidTaskStateException
     */
    protected function pause($eventFlags) { return ITask::STATUS_PENDING; }

    /**
     * Process an EVENT_TIMEOUT event. The task has timed out
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     * @throws InvalidTaskStateException
     */
    protected function timeout($eventFlags) { return ITask::STATUS_ABORTED; }
}