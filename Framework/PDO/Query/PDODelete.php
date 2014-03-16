<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Query;
use CPath\Config;
use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Log;
use PDO;

class PDODelete extends PDOWhere {

    /** @var \PDOStatement */
    private $stmt=NULL;

    private $limit=NULL;
    public function __construct(PDOTable $Table, $limit=NULL) {
        parent::__construct($Table);
        $this->limit = $limit;
    }

    // TODO: move to PDOWhere ?
    public function execute() {
        if(!$this->stmt) {
            $sql = $this->getSQL();
            $this->stmt = $this->getDB()->prepare($sql);
            if(Config::$Debug)
                Log::v2(__CLASS__, $sql);
        }
        $this->stmt->execute($this->mValues);
        return $this;
    }

    public function getDeletedRows() { return $this->stmt->rowCount(); }

    public function getSQL() {
        if(!$this->mWhere)
            throw new \Exception("method where() was not called");
        $SQL = "DELETE FROM ".$this->getTable()
            .parent::getSQL()
            .($this->limit ? "\nLIMIT ".$this->limit : "");
//        if(Config::$Debug)
//            Log::v2(__CLASS__, $SQL);
        return $SQL;
    }
}