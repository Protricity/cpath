<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/21/2014
 * Time: 7:33 PM
 */
namespace CPath\Data\Schema\PDO;

class PDOUpdateBuilder extends PDOWhereBuilder
{
	private $mUpdateSQL = null;

	public function update($fieldName, $fieldValue=null) {
		if (is_array($fieldName)) {
			foreach ($fieldName as $k => $v)
				$this->update($v, is_int($k) ? null : $k);
			return $this;
		}

		if($fieldName !== null) {
			$fieldName = $fieldName . ' = ?';
			$this->bindValue($fieldValue);
		}

		if ($this->mUpdateSQL) {
			$this->mUpdateSQL .= ', ' . $fieldName;
		} else {
			$this->mUpdateSQL = "\n\tSET " . $fieldName;
		}

		return $this;
	}

	protected function getSQL() {
		if(!$this->mTableSQL)
			throw new \InvalidArgumentException("Table not set");
		if(!$this->mUpdateSQL)
			throw new \InvalidArgumentException("Update not set");
		if(!$this->mWhereSQL)
			throw new \InvalidArgumentException("Select not set");

		if($this->mFormat)
			return sprintf($this->mFormat, $this->mTableSQL, $this->mUpdateSQL, $this->mWhereSQL, $this->mLimitSQL);

		return
			("UPDATE " . $this->mTableSQL)
			. ($this->mUpdateSQL)
			. ($this->mWhereSQL)
			. ($this->mLimitSQL);
	}
}