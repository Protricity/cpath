<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/31/2015
 * Time: 7:44 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Response\IResponse;

class PDONotFoundException extends PDOQueryException
{
	const DEFAULT_HTTP_CODE = IResponse::HTTP_NOT_FOUND;
	function __construct(AbstractPDOQueryBuilder $Query, $message = null, $statusCode = null, \Exception $previous = null) {
		parent::__construct($Query, $message, $statusCode, $previous);
	}
}