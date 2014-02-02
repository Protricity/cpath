<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Columns\Template;

use CPath\Builders\Tools\BuildPHPClass;
use CPath\Exceptions\BuildException;
use CPath\Framework\PDO\Builders\Columns\BuildPDOColumn;
use CPath\Framework\PDO\Builders\Columns\ColumnArgumentNotFoundException;

class PDOSimpleColumnTemplate implements IPDOColumnTemplate {

    private $mName, $mMatch, $mCode=null;

    /** @var BuildPDOColumn[] */
    private $mColumns=array();

    function __construct($name, $match=true) {
        $this->mName = $name;
        $parts = explode('_', $name);

        if($match === true) {
            $match = '/' . implode('.*', $parts) . '/i';
        }
        $this->mMatch = $match;
    }


    /**
     * Attempt to match this template to an existing database column
     * @param BuildPDOColumn $BuildColumn the column instance
     * @return boolean true if column  matches template
     */
    function matchColumn(BuildPDOColumn $BuildColumn) {
        $name = $BuildColumn->Name;
        if($this->mMatch && preg_match($this->mMatch, $name) === 1)
            return true;

        if($name == $this->mName)
            return true;

        return false;
    }

    /**
     * Attempt to match this template to an column comment arg
     * @param String $arg the column instance
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
     * Final processing for PHP classes for this Template
     * @param BuildPHPClass $TablePHP
     * @param BuildPHPClass $ModelPHP
     * @throws BuildException
     * @return void
     */
    function processTemplatePHP(BuildPHPClass $TablePHP, BuildPHPClass $ModelPHP) {
        foreach($this->mColumns as $Column)
            $Column->processTemplatePHP($TablePHP, $ModelPHP);
    }

    /**
     * Process the column according to the template or add it to a queue to be processed with ::process()
     * @param BuildPDOColumn $Column
     * @return void
     */
    function addColumn(BuildPDOColumn $Column) {
        $this->mColumns[] = $Column;
    }
}

