<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/21/2014
 * Time: 7:33 PM
 */
namespace CPath\Data\Schema\PDO;

class PDOInsertBuilder extends AbstractPDOQueryBuilder
{
	private $mInsertSQL = null;
	private $mValuesSQL = null;

	public function insert($fields, $_field=null) {
		if(is_array($fields) || $_field !==null) {
			is_array($fields) ?: $fields = func_get_args();
			if(is_string(key($fields))) {
				$this->insert(array_keys($fields));
				$this->values(array_values($fields));
				return $this;
			}

			$DB = $this->getDatabase();
			foreach($fields as &$field)
				$field = $DB->quote($field);
			$fields = implode(', ', $fields);
		}

		if($fields[0] !== '(')
			$fields = "(" . $fields . ")";
		$this->mInsertSQL = " " . $fields;

		return $this;
	}

	public function values($value, $_value=null) {
		$values = is_array($value) ? $value : func_get_args();
		if($this->mValuesSQL) {
			$this->mValuesSQL .= ',';

		} else {
			$this->mValuesSQL = "\n\tVALUES ";
		}
		$this->mValuesSQL .= "\n\t(?" . str_repeat(', ?', sizeof($values)-1) . ")";
		foreach($values as $value)
			$this->bindValue($value);
		return $this;
	}

//
//	public function execValues($value, $_value=null) {
//		$values = is_array($value) ? $value : func_get_args();
//		$this->values($values);
//		return $this->execute();
//	}


	protected function getSQL() {
		if(!$this->mTableSQL)
			throw new \InvalidArgumentException("Table not set");
		if(!$this->mInsertSQL)
			throw new \InvalidArgumentException("Update not set");

		if($this->mFormat)
			return sprintf($this->mFormat, $this->mTableSQL, $this->mInsertSQL, $this->mValuesSQL);

		return "INSERT INTO "
			. ($this->mTableSQL)
			. ($this->mInsertSQL)
			. ($this->mValuesSQL);
	}
}