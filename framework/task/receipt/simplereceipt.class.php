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


final class SimpleReceipt implements IReceipt {

    const PARAM_CLASS = 0;
    const PARAM_DATA = 1;

    /** @var ITask */
    private $mTask;

    function __construct(ITask $Task) {
        $this->mTask = $Task;
    }

    /**
     * Returns the task state as recorded
     * @return ITask return the task state
     */
    function getTask() {
        return $this->mTask;
    }

    /**
     * Process the task receipt state
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     * @throws ReceiptException
     */
    function processReceiptState($eventFlags) {
        // TODO: Implement processReceiptState() method.
    }

//    /**
//     * EXPORT Object to a simple data structure to be used in var_export($data, true)
//     * @return mixed
//     */
//    function serialize() {
//        $arr = array (
//            static::PARAM_CLASS => get_class($this->mTask),
//            static::PARAM_DATA => $this->mTask->serialize(),
//        );
//        return json_encode($arr);
//    }
//
//    /**
//     * Unserialize and instantiate an Object with the stored data
//     * @param mixed $data the exported data
//     * @return \CPath\Framework\Data\Serialize\Interfaces\ISerializable|Object
//     */
//    static function unserialize($data) {
//        $Task = static::unserializeToTask($data);
//        $Inst = new static($Task);
//        return $Inst;
//    }

//    /**
//     * @param $data
//     * @return ITask
//     */
//    protected static function unserializeToTask($data) {
//        $arr = json_decode($data, JSON_OBJECT_AS_ARRAY);
//        /** @var ITask $Class */
//        $Class = $arr[0];
//        $taskData = $arr[1];
//
//        /** @var ITask $Task */
//        $Task = $Class::unserialize($taskData);
//        return $Task;
//    }
    /**
     * @return ITaskParameter[]
     */
    function getParameters()
    {
        // TODO: Implement getParameters() method.
    }
}
