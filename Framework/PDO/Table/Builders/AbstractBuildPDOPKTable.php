<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 1/28/14
 * Time: 11:31 AM
 */
namespace CPath\Framework\PDO\Table\Builders;

use CPath\Framework\Build\Code\BuildPHPClass;
use CPath\Framework\PDO\Builders\Models\BuildPHPModelClass;
use CPath\Framework\PDO\DB\PDODatabase;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Table\Model\Types\PDOPrimaryKeyModel;
use CPath\Framework\PDO\Table\Types\PDOPrimaryKeyTable;
use CPath\Framework\PDO\Util\PDOStringUtil;

abstract class AbstractBuildPDOPKTable extends AbstractBuildPDOTable {

    private $mPrimary;

    /**
     * Create a new PDOPrimaryKeyTable builder instance
     * @param String $name the table name
     * @param String $comment the table comment
     * @param $namespace
     * @param String|null $PDOTableClass the PDOTable class to use
     * @param String|null $PDOModelClass the PDOModel class to use
     */
    public function __construct($name, $comment, $namespace, $PDOTableClass=null, $PDOModelClass=null) {
        parent::__construct($name, $comment, $namespace,
            $PDOTableClass ?: PDOPrimaryKeyTable::cls(),
            $PDOModelClass ?: PDOPrimaryKeyModel::cls()
        );
    }

    /**
     * Process PHP classes for a PDO Builder
     * @param PDODatabase $DB
     * @param BuildPHPTableClass $PHPTable
     * @param BuildPHPModelClass $PHPModel
     * @return void
     */
    function processPHP(PDODatabase $DB, BuildPHPTableClass $PHPTable, BuildPHPModelClass $PHPModel) {
        $PHPTable->addConst('PRIMARY', $this->getPrimaryKeyColumn());
        parent::processPHP($DB, $PHPTable, $PHPModel);
    }

    /**
     * @param BuildPHPClass $PHPModel
     * @override
     */
    function processPHPModelGetSet(BuildPHPClass $PHPModel)
    {
        foreach ($this->getColumns() as $Column) {
            $ucName = PDOStringUtil::toTitleCase($Column->getName(), true);
            $PHPModel->addMethod('get' . $ucName, '', sprintf(' return $this->%s; ', strtolower($Column->getName())));
            if (!$Column->hasFlag(PDOColumn::FLAG_PRIMARY))
                $PHPModel->addMethod('set' . $ucName, '$value, $commit=true', sprintf(' return $this->updateColumn(\'%s\', $value, $commit); ', strtolower($Column->getName())));
            $PHPModel->addMethodCode();
        }
    }

    function getPrimaryKeyColumn() {
        if (!$this->mPrimary)
            foreach ($this->getColumns() as $Column)
                if ($Column->hasFlag(PDOColumn::FLAG_PRIMARY))
                    $this->mPrimary = $Column->getName();
        return $this->mPrimary;
    }
}

