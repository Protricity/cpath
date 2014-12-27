<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/22/2014
 * Time: 10:25 PM
 */
namespace CPath\Data\Schema\PDO;

class PDODuplicateRowException extends PDOQueryException
{
	private $mColumnName;
	function __construct(AbstractPDOQueryBuilder $Query, $columnName=null, $message=null, $statusCode = null, \Exception $previous = null) {
		parent::__construct($Query, $message, $statusCode, $previous);
		$this->mColumnName = $columnName;
	}

	public function getColumnName() {
		return $this->mColumnName;
	}
}

