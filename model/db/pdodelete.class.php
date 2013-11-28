<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Config;
use CPath\Log;
use PDO;

class PDODelete extends PDOWhere {
    /** @var \PDO */
    private $DB;
    /** @var \PDOStatement */
    private $stmt=NULL;
    private $limit=NULL;
    public function __construct($table, \PDO $DB, $limit=NULL) {
        parent::__construct($table);
        $this->DB = $DB;
        $this->limit = $limit;
    }

    // TODO: move to PDOWhere ?
    public function execute() {
        if(!$this->stmt) {
            $sql = $this->getSQL();
            $this->stmt = $this->DB->prepare($sql);
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
        $SQL = "DELETE FROM ".$this->mTable
            .parent::getSQL()
            .($this->limit ? "\nLIMIT ".$this->limit : "");
//        if(Config::$Debug)
//            Log::v2(__CLASS__, $SQL);
        return $SQL;
    }
}