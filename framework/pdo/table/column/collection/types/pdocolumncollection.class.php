<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Column\Collection\Types;

use CPath\Framework\PDO\Table\Column\Collection\Interfaces\IPDOColumnCollection;
use CPath\Framework\PDO\Table\Column\Exceptions\ColumnNotFoundException;
use CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn;
use CPath\Type\Collection\AbstractCollection;

class PDOColumnCollection extends AbstractCollection implements IPDOColumnCollection {

    /**
     * Add an IRole to the collection
     * @param IPDOColumn $Column
     * @return \CPath\Framework\PDO\Table\Column\Collection\Interfaces\IPDOColumnCollection return self
     */
    function add(IPDOColumn $Column) {
        $this->addItem($Column);
        return $this;
    }

    function has($name) {
        /** @var IPDOColumn $Column */
        foreach($this as $Column) {
            if($Column->getName() === $name)
                return true;
        }
        return false;
    }

    function get($name) {
        /** @var IPDOColumn $Column */
        foreach($this as $Column) {
            if($Column->getName() === $name)
                return $Column;
        }
        throw new ColumnNotFoundException("Column '$name' was not found");
    }

    /**
     * @param $flags
     * @return PDOColumnCollection
     */
    function byFlags($flags) {
        $Columns = new PDOColumnCollection();
        /** @var IPDOColumn $Column */
        foreach($this as $Column)
            if($Column->hasFlag($flags))
                $Columns->add($Column);

        return $Columns;
    }
}

