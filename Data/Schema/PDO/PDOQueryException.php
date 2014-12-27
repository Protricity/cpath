<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/25/2014
 * Time: 1:22 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Request\Exceptions\RequestException;

class PDOQueryException extends RequestException
{
	private $mQuery;

	public function __construct(AbstractPDOQueryBuilder $Query, $message = null, $statusCode = null, \Exception $previous = null) {
		$this->mQuery = $Query;
		parent::__construct($message, $statusCode, $previous);
	}
}

