<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Field\Collection;
use CPath\Framework\API\Field\Collection\Interfaces\IFieldCollection;
use CPath\Framework\API\Field\Interfaces\IField;
use CPath\Framework\Data\Collection\AbstractCollection;

/**
 * Class FieldCollection
 * @package CPath\Framework\API\Field\Collection
 */
class FieldCollection extends AbstractCollection implements IFieldCollection {

    /**
     * @param IField $Field
     */
    function add(IField $Field) {
        $this->addItem($Field);
    }

    /**
     * @return IField[]
     */
    function getFields() {
        return $this->getItems();
    }
}