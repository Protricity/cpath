<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Describable;

/**
 * Interface IDescribableAggregate
 * Warning: getDescribable might return a String or it might return an IDescribable instance which implements __toString().
 * Use CPath\Helpers\Describable::get($object) to return an IDescribable in all instances
 * @package CPath\Interfaces
 */
interface IDescribableAggregate {

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable();
}
