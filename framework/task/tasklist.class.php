<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task;

use Traversable;

class TaskList implements ITaskCollection {

    /** @var ITask[] */
    private $mList = array(), $mFilteredList = array();
    private $mResetNext=false;

    function __construct() {

    }

    /**
     * Add an ITask to the collection
     * @param ITask $Task
     * @return ITaskCollection return self
     */
    function add(ITask $Task) {
        $this->mList[] = $Task;
        $this->mFilteredList[] = $Task;
        return $this;
    }

    /**
     * Filter the task list by flags using AND logic
     * @param int $flags - flags to filter by
     * @return ITaskCollection return self
     */
    function byFlags($flags) {
        $list2 = array();
        if($this->mResetNext)
            $this->getTasks(true);

        foreach($this->mFilteredList as $Task)
            if($Task->getTaskFlags() & $flags)
                $list2[] = $Task;

        $this->mFilteredList = $list2;
        return $this;
    }

    /**
     * Filter the task list by flags using AND logic
     * @param int $flags - flags to filter by
     * @return ITaskCollection return self
     */
    function byAnyFlag($flags) {
        $list2 = array();
        if($this->mResetNext)
            $this->getTasks(true);

        foreach($this->mFilteredList as $Task)
            if($Task->getTaskFlags() | $flags)
                $list2[] = $Task;

        $this->mFilteredList = $list2;
        return $this;
    }

    /**
     * Filter the task list by class name
     * @param String $className - class name to filter by
     * @return ITaskCollection return self
     */
    function byClass($className) {
        $list2 = array();
        if($this->mResetNext)
            $this->getTasks(true);

        foreach($this->mFilteredList as $Task)
            if($className == get_class($Task))
                $list2[] = $Task;

        $this->mFilteredList = $list2;
        return $this;
    }

    /**
     * Filter the task list by a callback
     * @callback bool function(ITask $Task)
     * @param Callable|\Closure $callback - callback to filter by. Return === true to keep a task in the collection
     * @return ITaskCollection return self
     */
    function byCallback($callback) {
        $list2 = array();
        if($this->mResetNext)
            $this->getTasks(true);

        foreach($this->mFilteredList as $Task)
            if(true === $callback($Task))
                $list2[] = $Task;

        $this->mFilteredList = $list2;
        return $this;
    }

    /**
     * Return a list of tasks as filtered by filter* commands
     * @param boolean $reset if true, the filters will be reset
     * @return ITask[]
     */
    function getTasks($reset = true) {
        $list = $this->mFilteredList;
        if($reset) {
            $this->mFilteredList = $this->mList;
            $this->mResetNext = false;
        }
        return $list;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        $this->mResetNext = true;
        return new \ArrayIterator($this->getTasks(false));
    }
}