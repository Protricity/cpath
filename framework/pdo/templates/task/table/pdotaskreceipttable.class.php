<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Templates\User\Table;

use CPath\Framework\PDO\Table\PDOPrimaryKeyTable;
use CPath\Framework\Task\ITask;
use CPath\Framework\Task\Receipt\IReceipt;
use CPath\Framework\Task\Receipt\SimpleReceipt;


/**
 * Class PDOTaskReceiptTable
 * @package CPath\Framework\PDO
 */
abstract class PDOTaskReceiptTable extends PDOPrimaryKeyTable {

    // Task-specific column
    /** (primary int) The Task Receipt integer identifier */
    const COLUMN_ID = NULL;
    /** (int) The User ID integer identifier */
    const COLUMN_USER_ID = NULL;
    /** (string) The Receipt class */
    const COLUMN_CLASS = NULL;
    /** (string) The Data Column */
    const COLUMN_DATA = NULL;


    /**
     * Add a task receipt to the database
     * @param $userID
     * @param IReceipt $Receipt
     * @return void
     */
    function insertReceipt($userID, IReceipt $Receipt) {
        $this->insert(static::COLUMN_USER_ID, static::COLUMN_CLASS, static::COLUMN_DATA)
            ->values($userID, get_class($Receipt), $Receipt->serialize());
    }

    /**
     * @param $userID
     * @param ITask $Task
     * @return IReceipt
     */
    function createReceiptFromTask($userID, ITask $Task) {
        $Receipt = new SimpleReceipt($Task);
        $this->insertReceipt($userID, $Receipt);
        return $Receipt;
    }
}