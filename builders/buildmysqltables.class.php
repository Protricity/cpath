<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Builders;
use CPath\DataBase\PostGreSQL;
use CPath\BuildException;
use CPath\Base;
use CPath\Build;
use CPath\Interfaces\IBuilder;
use CPath\Log;


class BuildMySQLTables extends BuildPDOTables {
    const DB_CLASSNAME = "CPath\\Model\\DB\\Mysql";

    protected function getTables(\PDO $DB){
        $tables = array();
        foreach($DB->query("SHOW TABLES") as $row) {
            $tables[] = array_pop($row);
        }
        return $tables;
    }

    protected function getColumns(\PDO $DB, $table) {
        $cols = array();
        foreach($DB->query("SHOW COLUMNS FROM `$table`;") as $row) {
            $name = $row['Field'];
            $cols[$name] = '?';
            if(stripos($row['Key'], 'PRI') === 0)
                $cols[$name] = 'DEFAULT';
        }
        return $cols;
    }

    protected function getProcs(\PDO $DB) {
        $procs = array();
        foreach($DB->query(
                    "SELECT r.routine_name"
                        ."  FROM information_schema.routines r"
                        ."  LEFT JOIN information_schema.parameters p on r.specific_name = p.specific_name"
                        ."  WHERE p.parameter_mode = 'IN'"
                        ."  ORDER BY r.specific_name, p.ordinal_position") as $row) {
            $name = $row['routine_name'];
            $sname = $row['specific_name'];
            if(empty($procs[$sname])) $procs[$sname] = array($name);
            if($row['parameter_name'])
                $procs[$sname][] = $row['parameter_name'];
        }
        return $procs;
    }
}
