<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/16/14
 * Time: 7:59 PM
 */
namespace CPath\Framework\PDO\Table\Model\Exceptions;

use CPath\Framework\PDO\Table\Types\PDOTable;
use CPath\Response\IResponse;
use CPath\Response\IResponse;

class ModelNotFoundException extends \Exception implements IResponse
{
    public function __construct(PDOTable $Table, $search) {
        if(is_array($search))
            $search = implode(', ', $search);
        $msg = $Table->getModelName() . " was not found: {$search}";
        parent::__construct($msg, IResponse::HTTP_NOT_FOUND);
    }
}