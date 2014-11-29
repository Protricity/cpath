<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Column\Template\Types;

use CPath\Build\Exceptions\BuildException;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\Table\Builders\BuildPHPTableClass;
use CPath\Framework\PDO\Table\Column\Builders\BuildPDOColumn;
use CPath\Framework\PDO\Table\Column\Exceptions\ColumnArgumentNotFoundException;
use CPath\Framework\PDO\Table\Column\Template\Interfaces\IPDOColumnTemplate;
use CPath\Framework\PDO\Util\PDOStringUtil;

class PDOSimpleColumnTemplate implements IPDOColumnTemplate {

    const PHP_GET = <<<'PHP'
    function get%s() { $T = $this->table(); return $this->{$T::%s}; }
PHP;

    private $mName, $mMatch, $mCode=null, $mRequired=false;

    /** @var \CPath\Framework\PDO\Table\Column\Builders\BuildPDOColumn[] */
    private $mColumns=array();

    function __construct($name, $required=false, $match=true) {
        $this->mRequired = $required;
        $this->mName = $name;
        $parts = explode('_', $name);

        if($match === true) {
            $match = '/' . implode('.*', $parts) . '/i';
        }
        $this->mMatch = $match;
    }


    /**
     * Attempt to match this template to an existing database column
     * @param \CPath\Framework\PDO\Table\Column\Builders\BuildPDOColumn $BuildColumn the column inst
     * @return boolean true if column  matches template
     */
    function matchColumn(BuildPDOColumn $BuildColumn) {
        $name = $BuildColumn->mName;
        if($this->mMatch && preg_match($this->mMatch, $name) === 1)
            return true;

        if($name == $this->mName)
            return true;

        return false;
    }

    /**
     * Attempt to match this template to an column comment arg
     * @param String $arg the column inst
     * @return boolean true if column  matches template
     */
    function matchColumnArg($arg) {
        if($arg === $this->mName)
            return true;

        if(!$this->mCode) {
            $parts = explode('_', $this->mName);
            $this->mCode = '';
            foreach($parts as $part)
                $this->mCode .= $part[0];
        }

        if('c' . $this->mCode === $arg)
            return true;

        return false;
    }

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
    function processTemplatePHP(BuildPHPTableClass $TablePHP, BuildPHPModelClass $ModelPHP) {
        if($this->mRequired && sizeof($this->mColumns) === 0)
            throw new BuildException("Template parameter required ({$TablePHP->getName()}): " . $this->mName);

        foreach($this->mColumns as $Column) {
            $title = PDOStringUtil::toTitleCase($Column->getName(), true);
            if(!$ModelPHP->hasMethod('get'.$title))
                $ModelPHP->addMethod('get'.$title, '', sprintf('$T = $this->table(); return $this->{$T::' . $title . '};', $title, 'COLUMN_'.strtoupper($title)));
        }
//        foreach($this->mColumns as $Column)
//            $Column->processTemplatePHP($TablePHP, $ModelPHP);
    }

    /**
     * Process the column according to the template or add it to a queue to be processed with ::process()
     * @param \CPath\Framework\PDO\Table\Column\Builders\BuildPDOColumn $Column
     * @return void
     */
    function addColumn(BuildPDOColumn $Column) {
        $this->mColumns[] = $Column;
    }
}

