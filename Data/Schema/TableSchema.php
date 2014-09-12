<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/11/14
 * Time: 9:48 PM
 */
namespace CPath\Data\Schema;

use CPath\Build\ClassDocBlock;
use CPath\Build\DocTag;
use CPath\Build\PropertyDocBlock;
use CPath\Request\CLI\CommandString;

class TableSchema implements IReadableSchema
{
    const TABLE_TAG = 'table';
    const NAME_TAG = 'name';
    const COLUMN_TAG = 'column';
    const INDEX_TAG = 'index';
    const COMMENT_TAG = 'comment';

    private $mClass;

    public function __construct($class) {
        $this->mClass = $class;
        $DocBlock = new ClassDocBlock($class);
        if(!$DocBlock->hasTag(self::TABLE_TAG))
            throw new \InvalidArgumentException("Class '" . $this->mClass . "' does not have @" . self::TABLE_TAG . " doctag ");
    }

    public function writeSchema(IWritableSchema $DB) {
        $ClassDoc = new ClassDocBlock($this->mClass);
        $tableName = dirname(get_class($this));
        $tableComment = $ClassDoc->getComment(true);
        $tableTagValue = null;
        $tableArgs = array();

        foreach($ClassDoc->getAllTags() as $Tag) {
            switch($Tag->getName()) {
                case self::TABLE_TAG:
                    $tableArgs = CommandString::parseArgs($Tag->getArgString());
                    if(!empty($tableArgs['name']))
                        $tableName = $tableArgs['name'];
                    if(!empty($tableArgs['comment']))
                        $tableComment = $tableArgs['comment'];

                    break;
                case self::NAME_TAG:
                    $tableName = $Tag->getNextArg();
                    break;
                case self::COMMENT_TAG:
                    $tableComment = $Tag->getNextArg();
                    break;
            }
        }

        $argString = '';
        for($i=0; isset($tableArgs[$i]); $i++)
            $argString .= ($argString ? ' ' : '') . $tableArgs[$i];

        $DB->writeTable($tableName, $argString, $tableComment);

        $Class = new \ReflectionClass($this->mClass);
        foreach($Class->getProperties() as $Property) {
            $PropertyDoc = new PropertyDocBlock($Property);
            if($PropertyDoc->hasTag(self::COLUMN_TAG)) {
                $columnName = $Property->getName();
                $columnArgs = array();
                $columnComment = $PropertyDoc->getComment(true);

                /** @var DocTag[] $indexTags */
                $indexTags = array();

                foreach($PropertyDoc->getAllTags() as $Tag) {
                    switch($Tag->getName()) {
                        case self::COLUMN_TAG:
                            $columnArgs = CommandString::parseArgs($Tag->getArgString());
                            if(!empty($columnArgs['name']))
                                $columnName = $columnArgs['name'];
                            if(!empty($columnArgs['comment']))
                                $columnComment = $columnArgs['comment'];

                            break;
                        case self::INDEX_TAG:
                            $indexTags[] = $Tag;
                            break;
                    }
                }

                $argString = '';
                for($i=0; isset($columnArgs[$i]); $i++)
                    $argString .= ($argString ? ' ' : '') . $columnArgs[$i];

                $DB->writeColumn($columnName, $argString, $columnComment);

                foreach($indexTags as $Tag) {
                    $indexArgs = CommandString::parseArgs($Tag->getArgString());
                    $indexName = $tableName . '_' . $columnName . '_index';
                    $indexComment = null;
                    $columnList = $columnName;

                    if(!empty($indexArgs['name']))
                        $indexName = $indexArgs['name'];

                    if(!empty($indexArgs['comment']))
                        $indexComment = $indexArgs['comment'];

                    if(!empty($indexArgs['columns']))
                        $columnList = $indexArgs['columns'];

                    $argString = '';
                    for($i=0; isset($indexArgs[$i]); $i++)
                        $argString .= ($argString ? ' ' : '') . $indexArgs[$i];

                    $DB->writeIndex($indexName, $columnList, $argString, $indexComment);
                }
            }
        }
    }
}