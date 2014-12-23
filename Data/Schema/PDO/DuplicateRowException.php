<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/22/2014
 * Time: 10:25 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Request\Exceptions\RequestException;

class DuplicateRowException extends RequestException
{
	private $mColumnName;
	function __construct($columnName=null, $message=null, $statusCode = null, \Exception $previous = null) {
		parent::__construct($message, $statusCode, $previous);
		$this->mColumnName = $columnName;
	}

	public function getColumnName() {
		return $this->mColumnName;
	}
}