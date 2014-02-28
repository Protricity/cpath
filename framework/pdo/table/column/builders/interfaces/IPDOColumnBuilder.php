<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Column\Builders\Interfaces;

use CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn;

interface IPDOColumnBuilder extends IPDOColumn {

    /**
     * Set
     * @param $flag
     * @param bool $on
     * @return mixed
     */
    function setFlag($flag, $on=true);

}

