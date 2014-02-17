<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/16/14
 * Time: 7:06 PM
 */
namespace CPath\Framework\PDO\Table\Builders\Exceptions;

class TableArgumentNotFoundException extends \Exception
{
    public function __toString()
    {
        return $this->getMessage();
    }
}