<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Builders\Models;

use CPath\Builders\Tools\BuildPHPClass;
use CPath\Exceptions\BuildException;
use CPath\Framework\PDO\Builders\Models;
use CPath\Framework\PDO\Columns\PDOColumn;

class BuildPDOTable
{

    public $Name, $Title, $ModelClassName, $TableClassName, $Namespace, $ModelName, $Comment,
        $SearchWildCard, $SearchLimit, $SearchLimitMax, $AllowHandler = false, $Primary, $Template;
    protected $mColumns = array();
    protected $mUnfound = array();
    protected $mArgs = array();

    public function __construct($name, $comment)
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
    }

    function init()
    {
        if (!$this->Primary)
            foreach ($this->mColumns as $Column)
                if ($Column->Flags & PDOColumn::FLAG_PRIMARY)
                    $this->Primary = $Column->Name;
    }

    function processArgs()
    {


        foreach ($this->mArgs as $field) {
            list($name, $arg) = array_pad(explode(':', $field, 2), 2, NULL);
            switch (strtolower($name)) {
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
                    $this->processDefault($field);
            }
        }
        $this->mArgs = array();
    }

    function processDefault($field)
    {
        $this->mUnfound[] = $field;
    }

    function replace(array $matches)
    {
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
     * @return \CPath\Framework\PDO\Builders\Models\BuildPDOColumn[]
     */
    public function getColumns()
    {
        return $this->mColumns;
    }

    /**
     * @param $name
     * @return \CPath\Framework\PDO\Builders\Models\BuildPDOColumn
     * @throws BuildException if the column is not found
     */
    public function getColumn($name)
    {
        if (!isset($this->mColumns[$name]))
            throw new BuildException("Column '{$name}' not found" . print_r($this, true));
        return $this->mColumns[$name];
    }

    public function addColumn(Models\BuildPDOColumn $Column)
    {
        $this->mColumns[$Column->Name] = $Column;
    }

    function processModelPHP(BuildPHPClass $PHP)
    {
        $this->processArgs();
        $PHP->setExtend("CPath\\Model\\DB\\PDOModel");

        if ($this->mUnfound)
            throw new BuildException("Invalid Table Comment Token '" . implode('|', $this->mUnfound) . "' in Table '{$this->Name}'");

    }

    protected function req($name, $arg, $preg = NULL, $desc = NULL)
    {
        if (!$arg || ($preg && !preg_match($preg, $arg, $matches)))
            throw new BuildException("Table Comment Token {$name} must be in the format {{$name}:" . ($desc ? : $preg ? : 'value') . '}');
        if (!$preg)
            return $arg;
        array_shift($matches);
        return $matches;
    }
}