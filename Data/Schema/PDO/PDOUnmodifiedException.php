<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/25/2014
 * Time: 1:31 PM
 */
namespace CPath\Data\Schema\PDO;

class PDOUnmodifiedException extends PDOQueryException
{
	public function __construct(AbstractPDOQueryBuilder $Query, $message = null, $statusCode = null, \Exception $previous = null) {
		parent::__construct($Query, $message, $statusCode, $previous);
	}
}