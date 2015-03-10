<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 9:48 PM
 */
namespace CPath\Data\Schema;

use CPath\Build\ClassDocBlock;
use CPath\Build\Code\IConstructorArgs;
use CPath\Build\PropertyDocBlock;
use CPath\Request\CLI\CommandString;
use Site\DB\DBConfig;

class TableSchema implements IReadableSchema, IConstructorArgs
{
    const TABLE_TAG = 'table';
    const COLUMN_TAG = 'column';
    const INDEX_TAG = 'index';
    const PRIMARY_TAG = 'primary';
	const UNIQUE_TAG = 'unique';
	const SELECT_TAG = 'select';

    private $mClass;

	/**
	 * @param string|object $Schema
	 */
    public function __construct($Schema) {
        $this->mClass = is_string($Schema) ? $Schema : get_class($Schema);
        $DocBlock = new ClassDocBlock($this->mClass);
        if(!$DocBlock->hasTag(self::TABLE_TAG))
            throw new \InvalidArgumentException("Class '" . $this->mClass . "' does not have @" . self::TABLE_TAG . " doctag ");
    }

    public function writeSchema(IWritableSchema $DB) {
        if(!DBConfig::$DB_WRITE_TABLES)
            return;

        $ClassDoc = new ClassDocBlock($this->mClass);
        $tableName = null;
        $tableTagValue = null;

        foreach($ClassDoc->getAllTags() as $Tag) {
            switch($Tag->getName()) {
                case self::TABLE_TAG:
                    $args = CommandString::parseArgs($Tag->getArgString());
                    $tableName = isset($args['name'])         ? $args['name']       : null;
//                    $tableComment = isset($args['comment'])   ? $args['comment']    : null; //$ClassDoc->getComment(true);

                    if(!$tableName && !empty($args[0]))
                        $tableName = array_shift($args);

                    $argString = '';
                    for($i=0; isset($args[$i]); $i++)
                        $argString .= ($argString ? ' ' : '') . $args[$i];

                    $DB->writeTable($this, $tableName, $argString, $ClassDoc->getComment());
                    break;
            }
        }

	    $indexes = array();

        $Class = new \ReflectionClass($this->mClass);
        foreach($Class->getProperties() as $Property) {
            $PropertyDoc = new PropertyDocBlock($Property);
            if($PropertyDoc->hasTag(self::COLUMN_TAG)) {

                $columnName = null;
                foreach($PropertyDoc->getAllTags() as $Tag) {
                    switch($Tag->getName()) {
                        case self::COLUMN_TAG:
                            $args = CommandString::parseArgs($Tag->getArgString());
                            $columnName = isset($args['name'])        ? $args['name']       : $Property->getName();
                            //$columnComment = isset($args['comment'])  ? $args['comment']    : null; //$PropertyDoc->getComment(true);

                            $argString = '';
                            for($i=0; isset($args[$i]); $i++)
                                $argString .= ($argString ? ' ' : '') . $args[$i];

                            $DB->writeColumn($this, $columnName, $argString, $PropertyDoc->getComment());
                            break;

                        case self::PRIMARY_TAG:
                        case self::UNIQUE_TAG:
                        case self::INDEX_TAG:
                            $args = CommandString::parseArgs($Tag->getArgString());

                            switch($Tag->getName()) {
                                case self::PRIMARY_TAG:
                                    $args[] = 'PRIMARY KEY';
                                    break;
                                case self::UNIQUE_TAG:
                                    $args[] = 'UNIQUE';
                                    break;
                            }

                            $indexName = isset($args['name'])        ? $args['name']       : $tableName . '_' . ($columnName ?: $Property->getName()) . '_' . $Tag->getName();
//                            $indexComment = isset($args['comment'])  ? $args['comment']    : $PropertyDoc->getComment(true);
                            $columnList = isset($args['columns'])    ? $args['columns']    : $Property->getName();

                            $argString = '';
                            for($i=0; isset($args[$i]); $i++)
                                $argString .= ($argString ? ' ' : '') . $args[$i];
							if(isset($indexes[$indexName])) {
								$indexes[$indexName][1] .= ', ' . $columnList;
							} else {
								$indexes[$indexName] = array($indexName, $columnList, $argString, $PropertyDoc->getComment());
							}

                            break;
                    }
                }
            }
        }

	    foreach($indexes as $index) {
		    list($indexName, $columnList, $argString, $comment) = $index;
		    $DB->writeIndex($this, $indexName, $columnList, $argString, $comment);
	    }
    }

	/**
	 * Return a list of args that could be called to initialize this class object
	 * @return array
	 */
	function getConstructorArgs() {
		return array($this->mClass);
	}
}