<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Actions;

use CPath\Base;
use CPath\Describable\IDescribableAggregate;
use CPath\Handlers\Fragments\IRenderFragmentContent;
use CPath\Handlers\Interfaces\IView;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Serializer\ISerializable;

abstract class Action implements IActionable, IRenderFragmentContent {

    private $mFlags = 0;

    public function __construct($allowRetryOnError=false) {
        if($allowRetryOnError)
            $this->setStatusFlags(IActionable::STATUS_ALLOW_RETRY);

        $this->setStatusFlags(IActionable::STATUS_ACTIVE, !$this->isAvailable());
    }

    /**
     * Provide head elements to any IView
     * Note: If an IView encounters this object, it should attempt to add support scripts to it's header by using this method
     * @param IView $View
     */
    function addHeadElementsToView(IView $View) {
        $basePath = Base::getClassPublicPath($this, false);
        $View->addHeadStyleSheet($basePath . 'assets/actions.css', true);
        $View->addHeadScript($basePath . 'assets/actions.js', true);
    }

    /**
     * Filter this action according to the present circumstances
     * @return bool true if this action is available. Return not true if this action is not available
     */
    abstract protected function isAvailable();

    /**
     * Filter this action according to the present circumstances
     * @param IRequest $Request
     * @return bool true if this action should execute. Return not true if this action does not apply
     */
    abstract function execute(IRequest $Request);

    /**
     * Called when an exception occurred. This should capture exceptions that occur in ::execute and ::filter
     * @param IRequest $Request
     * @param \Exception $Ex
     * @return void
     */
    abstract protected function onException(IRequest $Request, \Exception $Ex);

    /**
     * Called when a request to store the action in persistent data has been made.
     * Warning: This method may perform storage of the action in rapid succession.
     * @param IRequest $Request
     * @return void
     */
    abstract protected function onStore(IRequest $Request);

    /**
     * Return the action status flags.
     * Note: returning STATUS_ACTIVE or STATUS_EXPIRED should avoid having doAction(...) immediately afterwards.
     * Returning STATUS_EXPIRED also signals to the container that the action needs to be removed from the queue and deleted.
     * @return bool true if expired and false if not expired
     */
    final function getStatusFlags() {
        return $this->mFlags;
    }

    /**
     * Set flags for this action.
     * Setting of IActionable::FLAG_* flags is discouraged as it may interfere with internal settings.
     * @param int $flags the flags to set
     * @param bool $remove set true to remove flags
     * @throws \InvalidArgumentException
     */
    final function setStatusFlags($flags, $remove=false) {
        if($remove) {
            $this->removeStatusFlags($remove);
            return;
        }
        if(!is_int($flags))
            throw new \InvalidArgumentException("setFlags 'flags' parameter must be an integer");
        $this->mFlags |= $flags;
    }

    /**
     * Remove flags for this action.
     * Setting of IActionable::FLAG_* flags is discouraged as it may interfere with internal settings.
     * @param int $flags the flags to set
     * @throws \InvalidArgumentException
     */
    final function removeStatusFlags($flags) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("setFlags 'flags' parameter must be an integer");
        $this->mFlags = $this->mFlags & ~$flags;
    }

    final function setActive($enabled=true) { $this->setStatusFlags(IActionable::STATUS_ACTIVE, $enabled); }
    final function setExpired($expired=true) { $this->setStatusFlags(IActionable::STATUS_EXPIRED, $expired); }

    /**
     * Execute the action, and update the Status flags if necessary.
     * If the action has executed successfully (or otherwise needs to be removed), setting STATUS_EXPIRED would provide
     * the signal to remove the action from the queue
     * @param IRequest $Request
     * @return String|IResponse the action response
     * @throws InvalidActionStateException if the action is in the wrong state to execute
     * @throws \Exception if an error occurred
     */
    function executeAction(IRequest $Request) {
        $f = $this->mFlags;
        try {
            if($f & IActionable::STATUS_COMPLETE)
                throw new InvalidActionStateException("Completed action attempted to execute: " . $this);
            if($f & IActionable::STATUS_EXPIRED)
                throw new InvalidActionStateException("Expired action attempted to execute: " . $this);
            if(($f & IActionable::STATUS_ERROR) && !($f & IActionable::STATUS_ALLOW_RETRY))
                throw new InvalidActionStateException("An action that resulted in an error was attempted again. To allow retry, flag object with ::STATUS_ALLOW_RETRY: " . $this);

            $this->setStatusFlags(IActionable::STATUS_PROCESSING);
            $this->onStore($Request);

            $this->execute($Request);

            $this->setStatusFlags(IActionable::STATUS_COMPLETE);

            $this->removeStatusFlags(IActionable::STATUS_ABORTED);
            $this->removeStatusFlags(IActionable::STATUS_ERROR);
            $this->removeStatusFlags(IActionable::STATUS_PROCESSING);

            $this->onStore($Request);

        } catch (ActionAbortedException $ex) {
            $this->setStatusFlags(IActionable::STATUS_ABORTED);
            $this->removeStatusFlags(IActionable::STATUS_PROCESSING);

            $this->onStore($Request);

        } catch (\Exception $ex) {
            $this->setStatusFlags(IActionable::STATUS_ERROR);
            $this->removeStatusFlags(IActionable::STATUS_PROCESSING);

            $this->onStore($Request);

            $this->onException($Request, $ex);
            throw $ex;
        }
    }

    /**
     * EXPORT Object to a simple data structure to be used in var_export($data, true)
     * @return mixed
     */
    function serialize()
    {
        $data = array();
        foreach($this as $key => $val)
            $data[$key] = $val;
    }

    /**
     * Unserialize and instantiate an Object with the stored data
     * @param mixed $data the exported data
     * @return ISerializable|Object
     */
    static function unserialize($data)
    {
        $Inst = new static();
        foreach($data as $key => $val)
            $Inst->$key = $val;
        return $Inst;
    }
}
