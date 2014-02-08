<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Builders;
use CPath\Builders\Tools\BuildCSharpClass;
use CPath\Framework\PDO\Builders\Columns\BuildPDOColumn;
use CPath\Framework\PDO\Builders\Tables\BuildPDOTable;
use CPath\Framework\PDO\Columns\PDOColumn;
use CPath\Framework\PDO\Util\PDOStringUtil;

class BuildCSharpTables {

    private $mNamespace;

    public function __construct($namespace=NULL) {
        $this->mNamespace = $namespace;
    }

    /**
     * Builds C# class references for existing database tables
     * @param BuildPDOTable $Table
     * @param String $filePath build file path
     * @return boolean True if the class was built. False if it was ignored.
     * @throws \CPath\Exceptions\BuildException when a build exception occurred
     */
    public function build(BuildPDOTable $Table, $filePath) {

        $skip = true;
        $CS = new BuildCSharpClass($Table->ModelClassName, str_replace('\\', '.', $this->mNamespace ?: $Table->Namespace));

        $CS->addUse('System');
        $CS->addUse('System.Collections.Generic');
        $CS->addUse('System.Linq');
        $CS->addUse('System.Runtime.Serialization');

        $CS->addDataContractAttribute($Table->Name);

        //$CS->addConst('PRIMARY', $Table->Primary);


        //$CS->addConstCode();
        //$CS->addConstCode("// Table Columns ");
        //foreach($Table->getColumns() as $Column)
        //    $CS->addConst($this->toTitleCase($Column->Name, true), $Column->Name);

        /** @var BuildPDOColumn[] $Columns */
        $Columns = array();

        foreach($Table->getColumns() as $key => $Column)
            if($Column->Flags & PDOColumn::FLAG_EXPORT)
                $Columns[$key] = $Column;

        foreach($Columns as $Column)
            if($Column->EnumConstants) {
                $CS->addConstCode();
                $CS->addConstCode("// Column Enum Values for '" . $Column->Name ."'");
                foreach($Column->EnumValues as $enum)
                    $CS->addConst(PDOStringUtil::toTitleCase($Column->Name, true) . '_Enum_' . PDOStringUtil::toTitleCase($enum, true), $enum);
            }

        foreach($Columns as $Column) {
            $skip = false;
            $CS->addPropertyCode("/// <summary>" . $Column->Comment . "</summary>");
            $CS->addDataMemberAttribute($Column->Name);
            $CS->addProperty(PDOStringUtil::toTitleCase($Column->Name, true), null, 'public');
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
