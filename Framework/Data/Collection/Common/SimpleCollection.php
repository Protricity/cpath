<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Data\Collection;

class SimpleCollection extends AbstractCollection {

    /**
     * Add an item to the collection
     * @param ICollectionItem $Item
     * @return AbstractCollection return self
     */
    public function add(ICollectionItem $Item) {
        return $this->addItem($Item);
    }
}
