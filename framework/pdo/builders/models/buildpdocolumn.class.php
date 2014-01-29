<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:30 AM
 */
namespace CPath\Framework\PDO\Builders\Models;

use CPath\Exceptions\BuildException;
use CPath\Framework\PDO\Columns\PDOColumn;

class BuildPDOColumn
{
    public $Name, $Comment, $Flags = 0, $EnumValues, $Filter = NULL, $Default = NULL, $EnumConstants = false;

    public function __construct($name, $comment) {
        $this->Name = $name;
        $comment = preg_replace_callback('/\s*{([^}]*)}\s*/', array($this, 'replace'), $comment);
        if (!$this->Comment)
            $this->Comment = $comment;
        if ($this->Comment)
            $this->Comment = str_replace(';', ':', $this->Comment);
    }

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
                    throw new BuildException("Unrecognized Flag: " . $args[0] . " for column '" . $this->Name . "'");
            }
        }
        return '';
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