<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:30 AM
 */
namespace CPath\Framework\PDO\Table\Column\Builders;

use CPath\Exceptions\BuildException;
use CPath\Framework\Api\Field\Interfaces\IField;
use CPath\Framework\Exception\Common\NotImplementedException;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Column\Exceptions\ColumnArgumentNotFoundException;

class BuildPDOColumn extends AbstractBuildPDOColumn
{
    /**
     * Process unrecognized table comment arguments
     * @param String $arg the argument to process
     * @return void
     * @throws ColumnArgumentNotFoundException if the argument was not recognized
     */
    function processColumnArg($arg) {}

    /**
     * Additional processing for PHP classes for a PDO Builder Template
     * @param \CPath\Framework\PDO\Table\Builders\BuildPHPTableClass $TablePHP
     * @param BuildPHPModelClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPTableClass $TablePHP, BuildPHPModelClass $ModelPHP) {}

    /**
     * Generate an IField for this column
     * @param boolean|NULL $comment
     * @param mixed $defaultValidation
     * @param int $flags optional IField:: flags
     * @throws \Exception
     * @internal param bool|NULL $required if null, the column flag FLAG_REQUIRED determines the value
     * @internal param bool|NULL $param
     * @return IField
     */
    function generateAPIField($comment = NULL, $defaultValidation = NULL, $flags = 0) {
        throw new NotImplementedException();
    }
}