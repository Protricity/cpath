<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
use CPath\Base;
use CPath\Interfaces\IDatabase;
use CPath\Log;
use \PDO;
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

    public function execute() {
        if(!$this->stmt) $this->stmt = $this->DB->prepare($this->getSQL());
        $this->stmt->execute($this->values);
        return $this;
    }

    public function getDeletedRows() { return $this->stmt->rowCount(); }

    public function getSQL() {
        if(!$this->where)
            throw new \Exception("method where() was not called");
        $SQL = "DELETE FROM ".$this->table
            .parent::getSQL()
            .($this->limit ? "\nLIMIT ".$this->limit : "");
        if(Base::isDebug())
            Log::v(__CLASS__, $SQL);
        return $SQL;
    }
}