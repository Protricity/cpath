<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Type\Collection\Util;

use CPath\Type\Collection\ICollection;
use CPath\Type\Collection\IPredicate;
use CPath\Type\Collection\Predicates\InversePredicate;
use Traversable;

class CollectionUtil implements ICollectionUtil {
    private $mCollection;

    function __construct(ICollection $Collection) {
        $this->mCollection = $Collection;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return $this->mCollection->getIterator();
    }

    /**
     * Return true if at least one item matches the predicate
     * @param IPredicate $Where
     * @return boolean
     */
    function any(IPredicate $Where) {
        return $this
            ->mCollection
            ->where($Where)
            ->count() >= 1;
    }

    /**
     * Return true if all items match the predicate
     * @param IPredicate $Where
     * @return boolean
     */
    function all(IPredicate $Where) {
        $Col = $this->mCollection;
        return $Col
            ->where($Where)
            ->count() === $Col->count();
    }

    /**
     * Filter the item collection by an IPredicate
     * @param IPredicate $Where
     * @return ICollection
     */
    function where(IPredicate $Where) {
        return $this
            ->mCollection
            ->where($Where);
    }

    /**
     * Return the number of items in the collection optionally filtered by the predicate
     * @param IPredicate|null $Where optional filter
     * @return int
     */
    function count(IPredicate $Where = Null) {
        return $this
            ->mCollection
            ->count();
    }

    /**
     * Permanently remove all filtered items from the collection
     * @param IPredicate|null $Where optional filter
     * @return ICollection return self
     */
    function remove(IPredicate $Where) {
        $Inverse = new InversePredicate($Where);
        return $this
            ->mCollection
            ->where($Inverse);
    }
}