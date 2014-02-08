<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:30 AM
 */
namespace CPath\Framework\PDO\Builders\Columns;

use CPath\Exceptions\BuildException;
use CPath\Framework\Interfaces\Constructable\Constructable;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Builders\Tables\BuildPHPTableClass;
use CPath\Framework\PDO\Builders\Tables\TableArgumentNotFoundException;
use CPath\Framework\PDO\Columns\PDOColumn;
use CPath\Framework\PDO\Columns\Template\IPDOColumnTemplate;

class ColumnArgumentNotFoundException extends \Exception {
    public function __toString() {
        return $this->getMessage();
    }
}

class ColumnTemplateException extends \Exception {}
class ColumnTemplateNotSetException extends ColumnTemplateException {}
class ColumnTemplateAlreadySetException extends ColumnTemplateException {}

abstract class AbstractBuildPDOColumn
{
    public $Name, $Comment, $Flags = 0, $EnumValues, $Filter = NULL, $Default = NULL, $EnumConstants = false;

    /** @var IPDOColumnTemplate */
    private $mTemplate;

    /**
     * Process unrecognized table comment arguments
     * @param String $arg the argument to process
     * @return void
     * @throws ColumnArgumentNotFoundException if the argument was not recognized
     */
    abstract function processColumnArg($arg);

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param BuildPHPTableClass $TablePHP
     * @param BuildPHPModelClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    abstract function processTemplatePHP(BuildPHPTableClass $TablePHP, BuildPHPModelClass $ModelPHP);

    public function __construct($name, $comment) {
        $this->Name = $name;
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', array($this, 'replace'), $comment);
        if (!$this->Comment)
            $this->Comment = $comment;
        if ($this->Comment)
            $this->Comment = str_replace(';', ':', $this->Comment);
    }

    /**
     * Get the assigned template or throw an exception
     * @throws ColumnTemplateNotSetException if the template was not set
     * @return IPDOColumnTemplate
     */
    function getTemplate() {

        if(!$this->hasTemplate())
            throw new ColumnTemplateNotSetException("Template not set for: " . $this->Name);

        return $this->mTemplate;
    }

    /**
     * Set the template for this column
     * @param IPDOColumnTemplate $Template
     * @throws ColumnTemplateNotSetException
     */
    function setTemplate(IPDOColumnTemplate $Template) {

        if($this->hasTemplate())
            throw new ColumnTemplateNotSetException("Template already set for: " . $this->Name);

        $this->mTemplate = $Template;
    }

    /**
     * Does this column builder have a template?
     * @return bool
     */
    function hasTemplate() { return $this->mTemplate ? true : false; }

    function replace(array $matches) {
        foreach (explode('|', $matches[1]) as $field) {
            $args = explode(':', $field, 2);
            switch (strtolower($args[0])) {
                case 'ce':
                case 'constant_enums':
                    $this->EnumConstants |= PDOColumn::FLAG_INSERT;
                    break;
                case 'i':
                case 'insert':
                    $this->Flags |= PDOColumn::FLAG_INSERT;
                    break;
                case 'u':
                case 'update':
                    $this->Flags |= PDOColumn::FLAG_UPDATE;
                    break;
                case 's':
                case 'search':
                    $this->Flags |= PDOColumn::FLAG_SEARCH;
                    break;
                case 'e':
                case 'export':
                    $this->Flags |= PDOColumn::FLAG_EXPORT;
                    break;
                case 'r':
                case 'required':
                    $this->Flags |= PDOColumn::FLAG_REQUIRED;
                    break;
                case 'o':
                case 'optional':
                    $this->Flags &= ~PDOColumn::FLAG_REQUIRED;
                    $this->Flags |= PDOColumn::FLAG_OPTIONAL;
                    break;
                case 'c':
                case 'comment':
                    $this->Comment = $this->req($args);
                    break;
                case 'd':
                case 'default':
                    $this->Default = $this->req($args);
                    break;
                case 'f':
                case 'filter':
                    $filter = $this->req($args);
                    if (!is_numeric($filter))
                        $filter = constant($filter);
                    $this->Filter |= (int)$filter;
                    break;
                default:
                    if($this->hasTemplate()) {
                        $this->getTemplate()
                            ->processColumnArg($field);
                        break;
                    }

                    try {
                        $this->processColumnArg($field);
                    } catch (TableArgumentNotFoundException $ex) {
                        throw new ColumnArgumentNotFoundException("Unrecognized Flag: " . $args[0] . " for column '" . $this->Name . "'");
                    }
                    break;
            }
        }
        return '';
    }

    function buildColumn() {
        return new PDOColumn($this->Name, $this->Flags, $this->Filter, $this->Comment, $this->Default, $this->EnumValues);
    }

    function exportConstructor() {
        return Constructable::exportToPHPCode($this->buildColumn());
    }

    private function req($args, $preg = NULL, $desc = NULL)
    {
        if (!isset($args[1]) || ($preg && !preg_match($preg, $args[1], $matches)))
            throw new BuildException("Column Comment Token {$args[0]} must be in the format {{$args[0]}:" . ($desc ? : $preg ? : 'value') . '}');
        if (!$preg)
            return $args[1];
        array_shift($matches);
        return $matches;
    }
}