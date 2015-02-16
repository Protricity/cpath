<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/22/2014
 * Time: 10:25 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Response\IResponse;

class PDODuplicateRowException extends PDOQueryException
{
	const DEFAULT_HTTP_CODE = IResponse::HTTP_CONFLICT;

	function __construct(AbstractPDOQueryBuilder $Query, $message=null, $statusCode = null, \Exception $previous = null) {
		parent::__construct($Query, $message, $statusCode, $previous);
	}
}

