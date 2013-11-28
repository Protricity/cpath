<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;
abstract class PDOWhere {
    const LOGIC_OR = 0x1; // Default logic between WHERE elements to "OR" instead of "AND"

    protected $mTable, $mAlias, $mLastAlias;
    protected $mWhere=array(), $mValues=array(), $mChr=97;
    protected $mOrderBy, $mGroupBy;
    private $mLastCond = true;
    private $mJoins = array();
    protected $mFlags = 0;

    public function __construct($table) {
        $this->mTable = $table;
        $this->mLastAlias = $this->mAlias = $this->getAlias($table);
    }

    protected function getAlias($table) {
        if(($p = strpos($table, ' ')) !== false)
            return substr($table, $p + 1);
        return $table;
    }

    /**
     * Adds a 'LEFT JOIN' from $sourceTable to $destTable
     * @param String $sourceTable the source table to join from
     * @param String $destTable the destination table to join to
     * @param String $sourceField The source field to join on. If $destField is omited, $sourceField represents the entire " ON ..." segment of the join.
     * @param String|null $destField The destination field to join on.
     * @return $this this instance
     */
    public function leftJoinFrom($sourceTable, $destTable, $sourceField, $destField=NULL) {
        $this->mLastAlias = $sourceTable;
        return $this->leftJoin($destTable, $sourceField, $destField);
    }

    /**
     * Adds a 'LEFT JOIN' from the last table joined to $destTable
     * @param String $destTable the table to join
     * @param String $sourceField The source field to join on. If $destField is omited, $sourceField represents the entire " ON ..." segment of the join.
     * @param String|null $destField The destination field to join on.
     * @return $this this instance
     */
    public function leftJoin($destTable, $sourceField, $destField=NULL) {
        $alias = $this->getAlias($destTable);
        if($destField != NULL) {
            if(strpos($sourceField, '.') === false)
                $sourceField = $this->mLastAlias . ".{$sourceField}";

            if(strpos($destField, '.') === false)
                $destField = $alias . ".{$destField}";

            $sourceField = "ON {$sourceField} = {$destField}";
        }
        $this->mLastAlias = $alias;
        $this->mJoins[] = "\nLEFT JOIN {$destTable} {$sourceField}";
        return $this;
    }

    /**
     * Adds a WHERE condition to the search
     * Examples:
     *  ->where('myfield="myvalue"')                        // WHERE table.myfield="myvalue"
     *  ->where('myfield', 'myvalue')                       // WHERE table.myfield = 'myvalue'
     *  ->where('myfield >', 'myvalue')                     // WHERE table.myfield > 'myvalue'
     *  ->where('? LIKE {}.myfield', 'myvalue', 'myalias')  // WHERE 'myvalue' LIKE myalias.myfield
     *  ->where('myfield', 'myvalue', 'myalias')            // WHERE myalias.myfield = 'myvalue'
     * @param $field String the field to search. May include comparison characters.
     * @param String $value The value to compare against.
     * @param String|null $alias The table alias to prepend to the $field. If $value is not set or $field contains
     * characters in '?.()', then the alias will not be prepended to the field.
     * If the string '{}' appears, it will be replaced with the alias
     * @return $this returns the query instance
     * @throws \InvalidArgumentException
     */
    public function where($field, $value, $alias=NULL) {
        $field = $this->getAliasedField($field, $alias);

        if(is_array($value)) {
            if(!$value)
                throw new \InvalidArgumentException("An empty array was passed to Column '{$field}'");
            $this->mValues = array_merge($this->mValues, $value);
            $field .= ' in (?' . str_repeat(', ?', sizeof($this->mValues) - 1) . ')';
        } else {
            $this->mValues[] = $value;
            if(strpos($field, '?') === false) {
                $e = '=';
                if(preg_match('/([=<>!]+|like)\s*$/i', $field))
                    $e = ' ';
                $field .= $e . '?';
            }
        }

        return $this->whereSQL($field, $alias);
    }


    /**
     * Adds a WHERE condition to the search
     * Examples:
     *  ->where('myfield="myvalue"')                        // WHERE table.myfield="myvalue"
     * @param String|null $sql The sql code to add to WHERE
     * characters in '?.()', then the alias will not be prepended to the field.
     * If the string '{}' appears, it will be replaced with the alias
     * @return $this returns the query instance
     * @throws \InvalidArgumentException
     */
    public function whereSQL($sql) {
        if(preg_match('/^(AND|OR|\(|\))$/i', $sql)) {
            if($sql == '(' && !$this->mLastCond)
                $this->mWhere[] = ($this->mFlags & self::LOGIC_OR) ? 'OR' : 'AND';
            if($sql != ')')
                $this->mLastCond = true;
            $this->mWhere[] = $sql;
            return $this;
        }

        if (!$this->mLastCond) {
            $this->mWhere[] = ($this->mFlags & self::LOGIC_OR) ? 'OR' : 'AND';
        }

        $this->mWhere[] = $sql;
        $this->mLastCond = false;
        return $this;
    }

    public function whereA(Array $array, $logic = 'AND', $alias=null) {
        $this->whereSQL('(');
        $i=0;
        foreach($array as $key => $val) {
            if($i)
                $this->whereSQL(strcasecmp($logic, 'AND')===0 ? 'AND' : 'OR');
            $this->where($key, $val, $alias);
        }
        $this->whereSQL(')');
        return $this;
    }

    public function whereAny(Array $array, $alias=null) {
        return $this->whereA($array, "OR", $alias);
    }

    public function whereAll(Array $array, $alias=null) {
        return $this->whereA($array, "AND", $alias);
    }

    /**
     * Set ORDER BY for this statement
     * @param String $field the field or sql to add to the statement
     * @param bool $desc if true ORDER BY [field] DESC
     * @param String|null $alias The table alias to prepend to the $field. If $value is not set or $field contains
     * characters in '.()', then the alias will not be prepended to the field.
     * If the string '{}' appears, it will be replaced with the alias
     * @return $this the query instance
     */
    function orderBy($field, $desc=false, $alias=NULL) {
        $this->mOrderBy = $field . ($desc !== false ? ($desc === true ? ' DESC' : ' '.$desc) : '');
        return $this;
    }

    /**
     * Set GROUP BY for this statement
     * @param String $field the field or sql to add to the statement
     * @param String|null $alias The table alias to prepend to the $field. If $value is not set or $field contains
     * characters in '.()', then the alias will not be prepended to the field.
     * If the string '{}' appears, it will be replaced with the alias
     * @return $this the query instance
     */
    function groupBy($field, $alias=NULL) {
        $this->mGroupBy = $this->getAliasedField($field, $alias);
        return $this;
    }

    /**
     * Set flags for this query instance
     * @param int $flags the flag or flags to set
     * @return $this the query instance
     * @throws \InvalidArgumentException
     */
    function setFlag($flags) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("setFlags 'flags' parameter must be an integer");
        $this->mFlags |= $flags;
        return $this;
    }

    /**
     * Unset flags for this query instance
     * @param int $flags the flag or flags to unset
     * @return $this the query instance
     * @throws \InvalidArgumentException
     */
    function unsetFlag($flags) {
        if(!is_int($flags))
            throw new \InvalidArgumentException("setFlags 'flags' parameter must be an integer");
        $oldFlags = $this->mFlags;
        $this->mFlags = $oldFlags & ~$flags;
        return $this;
    }

    /**
     * Return the SQL for this statement
     * @return string
     */
    public function getSQL() {
        return implode('', $this->mJoins)
            ."\nWHERE ".($this->mWhere ? implode(' ', $this->mWhere) : '1')
            .($this->mGroupBy ? "\nGROUP BY ".$this->mGroupBy : '')
            .($this->mOrderBy ? "\nORDER BY ".$this->mOrderBy : '');
    }

    /**
     * @param $field
     * @param String|null $alias The table alias to prepend to the $field. If $value is not set or $field contains
     * characters in '.()', then the alias will not be prepended to the field.
     * If the string '{}' appears, it will be replaced with the alias
     * @return mixed|string
     */
    protected function getAliasedField($field, $alias=null) {
        if(!$alias)
            $alias = $this->mAlias;
        $field = str_replace('{}', $alias, $field, $c); // TODO: bad news. change
        if($c==0 && !preg_match('/[.(]/', $field))
            $field = ($alias) . '.' . $field;
        return $field;
    }
}