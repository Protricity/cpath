<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Describable;


class IsNotDescribableException extends \Exception {}

final class Describable {

    /**
     * Check to see if an object is describable and optionally returns the instance.
     * @param Object $object the describable object
     * @param null|IDescribable $Describable if the instance was found, this variable is passed that reference
     * @return bool
     */
    static function is($object, &$Describable=NULL) {
        if($object instanceof IDescribableAggregate)
            $object = self::getAgg($object);

        if($object instanceof IDescribable) {
            $Describable = $object;
            return true;
        }
        return false;
    }

    /**
     * Get IDescribable instance of object or throw an exception
     * @param mixed $object the describable object
     * @param bool $allowDefault if the object is not describable, use a default description instead, otherwise throw exception
     * @return IDescribable an instance of IDescribable. This may or may not be the original object
     * @throws IsNotDescribableException if the object does not implement IDescribable
     */
    static function get($object, $allowDefault=true) {
        if($object instanceof IDescribableAggregate)
            $object = self::getAgg($object);

        if($object instanceof IDescribable)
            return $object;

        if($allowDefault)
            return new SimpleDescription($object);

        throw new IsNotDescribableException("Object type '" . get_class($object) . "' does not implement IDescribable");
    }

    private static function getAgg(IDescribableAggregate $agg) {
        $agg = $agg->getDescribable();
        if($agg instanceof IDescribableAggregate)
            return self::getAgg($agg);
        elseif(is_string($agg))
            $agg = new SimpleDescription($agg);
        elseif(!($agg instanceof IDescribable))
            throw new \InvalidArgumentException("getDescribable did not return a string or instance of IDescribable");
        return $agg;
    }

}
