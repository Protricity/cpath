<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:30 AM
 */
namespace CPath\Framework\PDO\Table\Column\Builders;

use CPath\Exceptions\BuildException;
use CPath\Framework\Interfaces\Constructable\Constructable;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Columns\Template\Exceptions\PDOColumnTemplateNotSetException;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Column\Builders\Interfaces\IPDOColumnBuilder;
use CPath\Framework\PDO\Table\Column\Exceptions\ColumnArgumentNotFoundException;
use CPath\Framework\PDO\Table\Column\Template\Interfaces\IPDOColumnTemplate;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Request\IRequest;


abstract class AbstractBuildPDOColumn implements IPDOColumnBuilder
{
    public $mName, $mComment, $mFlags = 0, $mEnumValues, $mFilter = NULL, $mDefault = NULL, $mEnumConstants = false;

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

    public function __construct($name, $comment)
    {
        $this->mName = $name;
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', array($this, 'replace'), $comment);
        if (!$this->mComment)
            $this->mComment = $comment;
        if ($this->mComment)
            $this->mComment = str_replace(';', ':', $this->mComment);
    }

    /**
     * Returns the column name
     * @return String the column name
     */
    function getName() { return $this->mName; }

    function getComment() { return $this->mComment; }

    function getDefaultValue() { return $this->mDefault; }

    /**
     * Returns true one or more flags are set, otherwise false
     * Note: multiple flags follows 'OR' logic. Only one flag has to match to return true
     * @param int $flag the flag or flags to check
     * @return bool true one or more flags are set, otherwise false
     */
    function hasFlag($flag) { return $this->mFlags & $flag ? true : false; }

    /**
     * Set
     * @param $flag
     * @param bool $on
     * @return $this
     */
    function setFlag($flag, $on = true)
    {
        $on
        ? $this->mFlags |= $flag
        : $this->mFlags &= ~$flag;
        return $this;
    }

    /**
     * Get the comment for this column
     * @return String comment
     */
    function getMComment() { return $this->mComment; }

    /**
     * Get the assigned template or throw an exception
     * @throws PDOColumnTemplateNotSetException if the template was not set
     * @return IPDOColumnTemplate
     */
    function getTemplate()
    {

        if (!$this->hasTemplate())
            throw new PDOColumnTemplateNotSetException("Template not set for: " . $this->mName);

        return $this->mTemplate;
    }

    /**
     * Set the template for this column
     * @param IPDOColumnTemplate $Template
     * @throws PDOColumnTemplateNotSetException
     */
    function setTemplate(IPDOColumnTemplate $Template)
    {

        if ($this->hasTemplate())
            throw new PDOColumnTemplateNotSetException("Template already set for: " . $this->mName);

        $this->mTemplate = $Template;
    }

    /**
     * Does this column builder have a template?
     * @return bool
     */
    function hasTemplate()
    {
        return $this->mTemplate ? true : false;
    }

    function replace(array $matches)
    {
        foreach (explode('|', $matches[1]) as $field) {
            $args = explode(':', $field, 2);
            switch (strtolower($args[0])) {
                case 'ce':
                case 'constant_enums':
                    $this->mEnumConstants = true;
                    break;
                case 'i':
                case 'insert':
                    $this->setFlag(PDOColumn::FLAG_INSERT);
                    break;
                case 'u':
                case 'update':
                    $this->setFlag(PDOColumn::FLAG_UPDATE);
                    break;
                case 's':
                case 'search':
                    $this->setFlag(PDOColumn::FLAG_SEARCH);
                    break;
                case 'e':
                case 'export':
                    $this->setFlag(PDOColumn::FLAG_EXPORT);
                    break;
                case 'r':
                case 'required':
                    $this->setFlag(PDOColumn::FLAG_REQUIRED);
                    break;
//                case 'o':
//                case 'optional':
//                    $this->Flags &= ~PDOColumn::FLAG_REQUIRED;
//                    $this->Flags |= PDOColumn::FLAG_OPTIONAL;
//                    break;
                case 'c':
                case 'comment':
                    $this->mComment = $this->req($args);
                    break;
                case 'd':
                case 'default':
                    $this->mDefault = $this->req($args);
                    break;
                case 'f':
                case 'filter':
                    $filter = $this->req($args);
                    if (!is_numeric($filter))
                        $filter = constant($filter);
                    $this->mFilter |= (int)$filter;
                    break;
                default:
                    if ($this->hasTemplate()) {
                        $this->getTemplate()
                            ->processColumnArg($field);
                        break;
                    }

                    //try {
                    $this->processColumnArg($field);
                    //} catch (TableArgumentNotFoundException $ex) {
                    //    throw new ColumnArgumentNotFoundException("Unrecognized Flag: " . $args[0] . " for column '" . $this->Name . "'");
                    //}
                    break;
            }
        }
        return '';
    }

    function buildColumn()
    {
        // TODO: cleanup/remove
//        if ($this->Flags & PDOColumn::FLAG_INDEX)
//            $this->Flags |= PDOColumn::FLAG_SEARCH;
//        if (!($this->Flags & PDOColumn::FLAG_NULL)
//            && !($this->Flags & PDOColumn::FLAG_AUTOINC)
//            && !($this->Flags & PDOColumn::FLAG_DEFAULT)
//            //&& !($Column->Flags & PDOColumn::FLAG_OPTIONAL)
//        )
//            $this->Flags |= PDOColumn::FLAG_REQUIRED;

        return new PDOColumn($this->mName, $this->mFlags, $this->mFilter, $this->mComment, $this->mDefault, $this->mEnumValues);
    }

    function exportConstructor() {
        $Column = $this->buildColumn();
        $constructorName = basename(get_class($Column));
        return Constructable::exportToPHPCode($Column, $constructorName);
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

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param \CPath\Request\IRequest $Request the request inst
     * @param String $fieldName the field name
     * @throws \Exception
     * @return mixed the formatted input field that passed validation
     */
    function validate(IRequest $Request, $fieldName)
    {
        throw new \Exception("Not implemented");
    }
}
