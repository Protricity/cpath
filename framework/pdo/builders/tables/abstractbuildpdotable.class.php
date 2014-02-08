<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Builders\Tables;

use CPath\Builders\Tools\BuildPHPClass;
use CPath\Exceptions\BuildException;
use CPath\Framework\PDO\Builders\Columns\BuildPDOColumn;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Columns\PDOColumn;
use CPath\Framework\PDO\Columns\Template\IPDOColumnTemplate;
use CPath\Framework\PDO\DB\PDODatabase;
use CPath\Framework\PDO\Model\Helpers\IPDOBuilder;
use CPath\Framework\PDO\Model\PDOModel;
use CPath\Framework\PDO\Table\PDOTable;
use CPath\Framework\PDO\Util\PDOStringUtil;

class TableArgumentNotFoundException extends \Exception {
    public function __toString() {
        return $this->getMessage();
    }
}

abstract class AbstractBuildPDOTable implements IPDOBuilder
{

    public $Name, $Title, $ModelClassName, $TableClassName, $Namespace, $ModelName, $Comment,
        $SearchWildCard, $SearchLimit, $SearchLimitMax, $AllowHandler = false, $Primary, $Template;

    /** @var BuildPDOColumn[] */
    protected $mColumns = array();
    protected $mUnfound = array();
    protected $mArgs = array();

    /** @var IPDOColumnTemplate[] */
    private $mColumnTemplates = array();

    private $mPDOTableClass, $mPDOModelClass;

    /**
     * Process unrecognized table comment arguments
     * @param String $arg the argument to process
     * @return void
     * @throws TableArgumentNotFoundException if the argument was not recognized
     */
    abstract function processTableArg($arg);

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @return void
     */
    abstract function processTemplatePHP(BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel);


    /**
     * Create a new PDOTable builder instance
     * @param String $name the table name
     * @param String $comment the table comment
     * @param null $PDOTableClass the PDOTable class to use
     * @param null $PDOModelClass the PDOModel class to use
     */
    public function __construct($name, $comment, $PDOTableClass=null, $PDOModelClass=null)
    {
        $this->Name = $name;
        $this->Title = ucwords(str_replace('_', ' ', $this->Name));
        $this->ModelName = $this->Title;
        $this->ModelClassName = str_replace(' ', '', $this->Title) . 'Model';
        $this->TableClassName = str_replace(' ', '', $this->Title) . 'Table';
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', array($this, 'replace'), $comment);
        if (!$this->Comment)
            $this->Comment = $comment;
        if ($this->Comment)
            $this->Comment = str_replace(';', ':', $this->Comment);

        $this->mPDOModelClass = $PDOModelClass ?: PDOModel::cls();
        $this->mPDOTableClass = $PDOTableClass ?: PDOTable::cls();
    }

    function init()
    {
        if (!$this->Primary)
            foreach ($this->mColumns as $Column)
                if ($Column->Flags & PDOColumn::FLAG_PRIMARY)
                    $this->Primary = $Column->Name;

        foreach ($this->mArgs as $field) {
            list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
            switch ($lcName = strtolower($name)) {
                case 'i':
                case 'insert':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_INSERT;
                    break;
                case 'u':
                case 'update':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_UPDATE;
                    break;
                case 's':
                case 'search':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_SEARCH;
                    break;
                case 'e':
                case 'export':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_EXPORT;
                    break;
                case 'r':
                case 'required':
                    foreach (explode(',', $this->req($name, $arg)) as $column)
                        $this->getColumn(trim($column))
                            ->Flags |= PDOColumn::FLAG_REQUIRED;
                    break;
                case 'sw':
                case 'searchwildcard':
                    $this->SearchWildCard = true;
                    break;
                case 'sl':
                case 'searchlimit':
                    list($this->SearchLimit, $this->SearchLimitMax) =
                        $this->req($name, $arg, '/^(\d+):(\d+)$/', '{default limit}:{max limit}');
                    break;
                case 'c':
                case 'comment':
                    $this->Comment = $this->req($name, $arg);
                    break;
                case 'n':
                case 'name':
                    $this->ModelName = $this->req($name, $arg);
                    break;
                case 'ah':
                case 'api':
                case 'allowhandler':
                    $this->AllowHandler = true;
                    break;
                default:
                    try {
                        $this->processTableArg($field);
                    } catch (TableArgumentNotFoundException $ex) {
                        $this->mUnfound[] = $ex;
                    }
            }
        }
        $this->mArgs = array();

        foreach ($this->getColumns() as $Column)
            if(!$Column->hasTemplate())
                foreach($this->mColumnTemplates as $Template)
                    if($Template->matchColumn($Column)) {
                        $Column->setTemplate($Template);
                        $Template->addColumn($Column);
                    }


    }

    function replace(array $matches) {
        foreach (explode('|', $matches[1]) as $field) {
            list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
            switch (strtolower($name)) {
                case 't':
                case 'template':
                    $this->Template = $this->req($name, $arg);
                    break;
                default:
                    $this->mArgs[] = $field;
            }
        }
        return '';
    }

    /**
     * @return BuildPDOColumn[]
     */
    public function getColumns() {
        return $this->mColumns;
    }

    /**
     * @param $name
     * @return BuildPDOColumn
     * @throws BuildException if the column is not found
     */
    public function getColumn($name) {
        if (!isset($this->mColumns[$name]))
            throw new BuildException("Column '{$name}' not found" . print_r($this, true));
        return $this->mColumns[$name];
    }

    public function addColumn(BuildPDOColumn $Column) {
        $this->mColumns[$Column->Name] = $Column;
    }

    /**
     * Add a custom column template to this builder
     * @param IPDOColumnTemplate $Template
     */
    public function addColumnTemplate(IPDOColumnTemplate $Template) {
        $this->mColumnTemplates[] = $Template;
    }

    /**
     * Process PHP classes for a PDO Builder
     * @param PDODatabase $DB
     * @param BuildPDOTable $Table
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @throws \CPath\Exceptions\BuildException
     * @return void
     */
    function processPHP(PDODatabase $DB, BuildPDOTable $Table, BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {
        //$this->processArgs();

        if ($this->mUnfound)
            throw new BuildException("Invalid Table Comment Token '" . implode('| ', $this->mUnfound) . "' in Table '{$this->Name}'");


        $PHPModel->addConst('MODEL_NAME', $Table->ModelName);
        $PHPModel->addConst('TABLE_CLASS', $Table->TableClassName);


        $PHPTable->setExtend($this->mPDOTableClass);
        $PHPModel->setExtend($this->mPDOModelClass);

        $this->processPHPTableConstructor($DB, $Table, $PHPTable);
        $this->processPHPTableConstants($Table, $PHPTable);
        $this->processPHPModelProperties($Table, $PHPModel);
        $this->processPHPModelGetSet($Table, $PHPModel);

        foreach($this->mColumns as $Column)
            $Column->processTemplatePHP($PHPTable, $PHPModel);

        foreach($this->mColumnTemplates as $Template)
            $Template->processTemplatePHP($PHPTable, $PHPModel);
    }

    function processPHPTableConstructor(PDODatabase $DB, BuildPDOTable $Table, BuildPHPClass $PHPTable) {

        $construct = 'parent::__construct(';

        $i = 0;

        foreach ($Table->getColumns() as $Column) {
            //$cols[] = $Column->exportConstructor();
            if ($i++) $construct .= ',';
            $construct .= "\n\t\t\t" . $Column->exportConstructor();
        }

        $construct .= ');';

        $PHPTable->addMethod('__construct', '', $construct);
        $PHPTable->addUse('CPath\Framework\PDO\PDOColumn');

        $PHPTable->addUse(get_class($DB), 'DB');
        $PHPTable->addStaticMethod('getDB', '', " return DB::get(); ");

//            foreach ($Table->getColumns() as $Column) {
//                if ($i++) $construct .= ',';
//                $construct .= "\n\t\t\tnew PDOColumn(";
//                $construct .= var_export($Column->Name, true);
//                $construct .= ',0x' . dechex($Column->Flags ? : 0);
//
//                if ($Column->Comment || $Column->Filter || $Column->Default || $Column->EnumValues)
//                    $construct .= ',' . ($Column->Filter ? : 0);
//                if ($Column->Comment || $Column->Default || $Column->EnumValues)
//                    $construct .= ',' . var_export($Column->Comment ? : '', true);
//                if ($Column->Default || $Column->EnumValues)
//                    $construct .= ',' . var_export($Column->Default ? : '', true);
//                if ($Column->EnumValues) {
//                    $a = '';
//                    foreach ($Column->EnumValues as $e)
//                        $a .= ($a ? ',' : '') . var_export($e, true);
//                    $construct .= ',array(' . $a . ')';
//                }
//                $construct .= ")";
//            }

    }

    function processPHPTableConstants(BuildPDOTable $Table, BuildPHPClass $PHPTable) {
        $PHPTable->addConstCode();
        $PHPTable->addConstCode("// Table Columns ");
        foreach ($Table->getColumns() as $Column)
            $PHPTable->addConst(PDOStringUtil::toTitleCase($Column->Name, true), $Column->Name);

        foreach ($Table->getColumns() as $Column)
            if ($Column->EnumConstants) {
                $PHPTable->addConstCode();
                $PHPTable->addConstCode("// Column Enum Values for '" . $Column->Name . "'");
                foreach ($Column->EnumValues as $enum)
                    $PHPTable->addConst(PDOStringUtil::toTitleCase($Column->Name, true) . '_Enum_' . PDOStringUtil::toTitleCase($enum, true), $enum);
            }

        $PHPTable->addConst('TABLE', $Table->Name);
        $PHPTable->addConst('MODEL_CLASS', $Table->ModelClassName);
        if ($Table->SearchWildCard)
            $PHPTable->addConst('SEARCH_WILDCARD', true);
        if ($Table->SearchLimit)
            $PHPTable->addConst('SEARCH_LIMIT', $Table->SearchLimit);
        if ($Table->SearchLimitMax)
            $PHPTable->addConst('SEARCH_LIMIT_MAX', $Table->SearchLimitMax);
        //if ($Table->AllowHandler)
        //$PHPTable->addImplements('CPath\Interfaces\IBuildable');
    }

    function processPHPModelProperties(BuildPDOTable $Table, BuildPHPClass $PHPModel) {
        foreach ($Table->getColumns() as $Column)
            $PHPModel->addProperty($Column->Name);
    }

    function processPHPModelGetSet(BuildPDOTable $Table, BuildPHPClass $PHPModel) {
        foreach ($Table->getColumns() as $Column) {
            $ucName = PDOStringUtil::toTitleCase($Column->Name, true);
            $PHPModel->addMethod('get' . $ucName, '', sprintf(' return $this->%s; ', strtolower($Column->Name)));
            if ($Column->Flags & PDOColumn::FLAG_PRIMARY ? 0 : 1 && $Table->Primary) // TODO: primary hack needs oop
                $PHPModel->addMethod('set' . $ucName, '$value, $commit=true', sprintf(' return $this->updateColumn(\'%s\', $value, $commit); ', strtolower($Column->Name)));
            $PHPModel->addMethodCode();
        }
    }

    protected function req($name, $arg, $preg = NULL, $desc = NULL) {
        if (!$arg || ($preg && !preg_match($preg, $arg, $matches)))
            throw new BuildException("Table Comment Token {$name} must be in the format {{$name}:" . ($desc ? : $preg ? : 'value') . '}');
        if (!$preg)
            return $arg;
        array_shift($matches);
        return $matches;
    }
}