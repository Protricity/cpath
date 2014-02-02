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
use CPath\Framework\PDO\Columns\Template\IPDOColumnTemplate;
use CPath\Framework\PDO\Columns\PDOColumn;
use CPath\Framework\PDO\Model\Helpers\IPDOBuilder;
use CPath\Framework\PDO\Model\PDOModel;
use CPath\Framework\PDO\Table\PDOTable;

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
     * @param BuildPHPClass $TablePHP
     * @param BuildPHPClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    abstract function processTemplatePHP(BuildPHPClass $TablePHP, BuildPHPClass $ModelPHP);


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
     * @param BuildPHPClass $TablePHP
     * @param BuildPHPClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    function processPHP(BuildPHPClass $TablePHP, BuildPHPClass $ModelPHP) {
        //$this->processArgs();

        if ($this->mUnfound)
            throw new BuildException("Invalid Table Comment Token '" . implode('| ', $this->mUnfound) . "' in Table '{$this->Name}'");

        $TablePHP->setExtend($this->mPDOTableClass);
        $ModelPHP->setExtend($this->mPDOModelClass);

        foreach($this->mColumnTemplates as $Template)
            $Template->processTemplatePHP($TablePHP, $ModelPHP);
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