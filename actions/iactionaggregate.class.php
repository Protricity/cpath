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
use CPath\Serializer\ISerializable;

interface IActionAggregate {

    /**
     * Load all available actions from this object into the action manager.
     */
    function loadActions(IActionManager $Manager);
}
