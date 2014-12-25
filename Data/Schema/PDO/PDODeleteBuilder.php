<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/21/2014
 * Time: 7:33 PM
 */
namespace CPath\Data\Schema\PDO;

class PDODeleteBuilder extends PDOWhereBuilder
{
	protected function getSQL() {
		if(!$this->mTableSQL)
			throw new \InvalidArgumentException("Table not set");
		if(!$this->mWhereSQL)
			throw new \InvalidArgumentException("Select not set");

		if($this->mFormat)
			return sprintf($this->mFormat, $this->mTableSQL, $this->mWhereSQL, $this->mLimitSQL);

		return "DELETE FROM "
			. ($this->mTableSQL)
			. ($this->mWhereSQL)
			. ($this->mLimitSQL);
	}
}