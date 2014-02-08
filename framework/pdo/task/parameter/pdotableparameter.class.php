<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Task\Parameter;

use CPath\Framework\Api\Exceptions\ValidationException;
use CPath\Framework\PDO\Table\PDOTable;

class PDOTableParameter implements ITaskParameter {

    private $mTable;

    function __construct(PDOTable $Table) {
        $this->mTable = $Table;
    }

    /**
     * Get the parameter value
     * @return String|PDOTable
     */
    function getValue() { return $this->mTable; }

    /**
     * Set the parameter value
     * @param String $value
     * @return mixed
     */
    function setValue($value) { $this->mValue = $value; }

    /**
     * Validate the parameter value
     * @return void
     * @throws ValidationException
     */
    function validate() {}
}
