<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Actions;

use CPath\Describable\IDescribableAggregate;
use CPath\Interfaces\IRequest;
use CPath\Response\IResponse;
use CPath\Interfaces\IViewConfig;
use CPath\Serializer\ISerializable;

interface IActionable extends ISerializable, IDescribableAggregate, IViewConfig {

    // States
    const STATUS_ACTIVE         = 0x001;    // Action is in active state
    const STATUS_PROCESSING     = 0x002;    // Action is currently being processed. If this is encountered before a new execution, the old execution may have failed
    const STATUS_COMPLETE       = 0x004;    // Action is in complete state
    const STATUS_EXPIRED        = 0x008;    // Action is expire and needs to be removed from the queue

    const STATUS_ABORTED        = 0x010;    // Action is currently in error state
    const STATUS_ERROR          = 0x020;    // Action is currently in error state

    // Types of actions
    const STATUS_SYSTEM         = 0x100;    // Action is a system message
    const STATUS_PRIORITY       = 0x200;    // Action is priority
    const STATUS_PERSISTENT     = 0x400;    // Action is persistent and should not be disposed of when complete
    const STATUS_IGNORED        = 0x800;    // Action is currently ignored

    // Options
    const STATUS_ALLOW_RETRY    = 0x10000;   // Action in 'error' state may be re-attempted

    /**
     * Return the action status flags.
     * Note: returning STATUS_ACTIVE or STATUS_EXPIRED should avoid having doAction(...) immediately afterwards.
     * Returning STATUS_EXPIRED also signals to the container that the action needs to be removed from the queue and deleted.
     * @return bool true if expired and false if not expired
     */
    function getStatusFlags();

    /**
     * Execute the action, and update the Status flags if necessary.
     * If the action has executed successfully (or otherwise needs to be removed), setting STATUS_EXPIRED would provide
     * the signal to remove the action from the queue
     * @param IRequest $Request
     * @return String|IResponse the action response
     */
    function executeAction(IRequest $Request);
}
