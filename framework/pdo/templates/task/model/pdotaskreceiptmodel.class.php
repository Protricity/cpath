<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Model;

use CPath\Framework\PDO\Model\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Templates\User\Table\PDOTaskReceiptTable;
use CPath\Framework\Task\Receipt\IReceiptManager;


/**
 * Class PDOTaskReceiptModel
 * A PDOTaskReceiptModel for ITask Tables
 * @package CPath\Framework\PDO
 */
abstract class PDOTaskReceiptModel extends PDOPrimaryKeyModel implements IReceiptManager {

    /**
     * @return PDOTaskReceiptTable
     */
    abstract function table();


//
//    /**
//     * UPDATE a column value for this Model
//     * @param String $column the column name to update
//     * @param String $value the value to set
//     * @param bool $commit set true to commit now, otherwise use ->commitColumns
//     * @return $this
//     */
//    function updateColumn($column, $value, $commit=true) {
//        $T = $this->table();
//        if($column == $T::COLUMN_PASSWORD)
//            $value = $T->hashPassword($value);
//        return parent::updateColumn($column, $value, $commit);
//    }
}