<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/21/2014
 * Time: 9:00 PM
 */
namespace CPath\Data\Schema\PDO;

abstract class PDOWhereBuilder extends AbstractPDOQueryBuilder
{
	protected $mWhereSQL = null;
	protected $mLimitSQL = null;

	public function where($whereColumn, $whereValue = null, $compare = '=?', $logic = 'AND') {
		if (is_array($whereColumn)) {
			foreach ($whereColumn as $k => $v)
				$this->where(is_int($k) ? null : $k, $v, $compare, $logic);

			return $this;
		}

		if($whereValue instanceof PDOSelectBuilder) {
			$sql = $whereValue->getSQL();
			$sql = preg_replace('/\n/m', "\n\t", "\n" . $sql);
			$whereColumn = $whereColumn . " in (" . $sql . "\n)";
			foreach($whereValue->getValues() as $k => $val)
				$this->bindValue($val, is_int($k) ? null : $k);

		} elseif ($whereValue) {
//			$compare === '=?' ?: $compare = '=:' . $whereColumn;
			$this->bindValue($whereValue);
			$whereColumn = $whereColumn . ' ' . $compare;
		}

		if ($this->mWhereSQL) {
			if (strcasecmp($logic, "AND") === 0) {
				$this->mWhereSQL .= "\n\t" . $logic . ' ' . $whereColumn;
			} else {
				$this->mWhereSQL = "\n\t(" . $this->mWhereSQL . "\n\t) " . $logic . ' ' . $whereColumn;
			}
		} else {
			$this->mWhereSQL = "\n\tWHERE " . $whereColumn;
		}

		return $this;
	}

	public function limit($limit, $offset = null) {
		if ($offset !== null)
			$limit = "{$limit} {$offset}";

		if(strpos($limit, 'LIMIT') === false)
			$limit = "\n\tLIMIT " . $limit;
		$this->mLimitSQL = $limit;
		return $this;
	}
}