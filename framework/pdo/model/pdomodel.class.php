<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Model;

use CPath\Config;
use CPath\Framework\PDO\Columns\PDOColumn;
use CPath\Serializer\ISerializable;
use CPath\Util;

abstract class PDOModel implements IPDOModel {
    const MODEL_NAME = null;
    const COLUMN_TITLE = NULL;   // Title column provides the column to use in a title


    /**
     * PDOModel Constructor parameters must be optional.
     * No queries should be attempted to load the model from the constructor.
     * Parameters may formatted and additional parameters added in the constructor
     */
    final public function __construct() {

    }

    /**
     * Get model value by column
     * @param String $column column name
     * @return mixed
     */
    function columnValue($column) {
        //$this->loadColumn($column);
        return $this->$column;
    }


    function toXML(\SimpleXMLElement $xml){
        foreach($this->exportData() as $key=>$val)
            if(is_scalar($val) || $val === null)
                $xml->addAttribute($key, $val);
            else {
                $xml2 = $xml->addChild($key);
                Util::toXML($val, $xml2);
            }
    }

    function toJSON(Array &$JSON){
        foreach($this->exportData() as $key=>$val)
            $JSON[$key] = Util::toJSON($val);
    }

    /**
     * Returns an associative array of columns and values for this object filtered by the tokens in constant EXPORT.
     * Defaults to just primary key, if exists.
     * @param mixed|NULL $columns array or list (comma delimited) of columns to export
     * @return Array
     */
    public function exportData($columns=NULL) {
        $export = array();
        foreach($this->table()->findColumns($columns ?: PDOColumn::FLAG_EXPORT) as $column => $data)
            $export[$column] = $this->$column;
        return $export;
    }

    /**
     * EXPORT Object to a simple data structure to be used in var_export($data, true)
     * @return mixed
     */
    function serialize()
    {
        $data = array();
        foreach($this->table()->getColumns() as $name => $Column)
            $data[$name] = $this->$name;
        return $data;
    }

    function __toString() {
        if($id = static::COLUMN_TITLE)
            return static::modelName() . " '" . $this->$id . "'";
        return static::modelName();
    }


    function __set($name, $value) {
        throw new \InvalidArgumentException("May not set undefined property '{$name}' to ".self::modelName());
    }

    function __get($name) {
        throw new \InvalidArgumentException("May not get undefined property '{$name}' to ".self::modelName());
    }

    // Statics

    /**
     * Returns the model name from comment or the class name
     * @return string the model name
     */
    public static function modelName() {
        return static::MODEL_NAME ?: basename(get_called_class());
    }

    /**
     * Unserialize and instantiate an Object with the stored data
     * @param mixed $data the exported data
     * @return ISerializable|Object
     */
    static function unserialize($data) {
        $Model = new static();
        foreach($data as $k=>$v)
            $Model->$k = $v;
        return $Model;
    }

    /**
     * Return the full class name via get_called_class
     * @return String the Class name
     */
    final static function cls() { return get_called_class(); }
}
