<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Builders;
use CPath\Build\Code\BuildCSharpClass;
use CPath\Framework\PDO\Table\Builders\Interfaces\IPDOTableBuilder;
use CPath\Framework\PDO\Table\Column\Builders\BuildPDOColumn;
use CPath\Framework\PDO\Table\Column\Types\PDOColumn;
use CPath\Framework\PDO\Util\PDOStringUtil;

class BuildCSharpTables {

    private $mNamespace;

    public function __construct($namespace=NULL) {
        $this->mNamespace = $namespace;
    }

    /**
     * Builds C# class references for existing database tables
     * @param IPDOTableBuilder $Table
     * @param String $filePath build file path
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\Exceptions\BuildException when a build exception occurred
     */
    public function build(IPDOTableBuilder $Table, $filePath) {

        $skip = true;
        $CS = new BuildCSharpClass(basename($Table->getModelClass()), str_replace('\\', '.', $this->mNamespace ?: $Table->getNamespace()));

        $CS->addUse('System');
        $CS->addUse('System.Collections.Generic');
        $CS->addUse('System.Linq');
        $CS->addUse('System.Runtime.Serialization');

        $CS->addDataContractAttribute($Table->getTableName());

        //$CS->addConst('PRIMARY', $Table->Primary);


        //$CS->addConstCode();
        //$CS->addConstCode("// Table Columns ");
        //foreach($Table->getColumns() as $Column)
        //    $CS->addConst($this->toTitleCase($Column->Name, true), $Column->Name);

        /** @var BuildPDOColumn[] $Columns */
        $Columns = array();

        /** @var BuildPDOColumn $Column // TODO: remove comment */
        foreach($Table->getColumns() as $key => $Column)
            if($Column->hasFlag(PDOColumn::FLAG_EXPORT))
                $Columns[$key] = $Column;

        foreach($Columns as $Column)
            if($Column->mEnumConstants) {
                $CS->addConstCode();
                $CS->addConstCode("// Column Enum Values for '" . $Column->getName() ."'");
                foreach($Column->mEnumValues as $enum)
                    $CS->addConst(PDOStringUtil::toTitleCase($Column->getName(), true) . '_Enum_' . PDOStringUtil::toTitleCase($enum, true), $enum);
            }

        foreach($Columns as $Column) {
            $skip = false;
            $CS->addPropertyCode("/// <summary>" . $Column->getComment() . "</summary>");
            $CS->addDataMemberAttribute($Column->getName());
            $CS->addProperty(PDOStringUtil::toTitleCase($Column->getName(), true), null, 'public');
            $CS->addPropertyCode();
        }

        if(!$skip)
            file_put_contents($filePath, $CS->build());
        return true;
    }

    static function createBuildableInstance() {
        return new static;
    }
}
