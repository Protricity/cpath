<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/19/2014
 * Time: 6:22 PM
 */
namespace CPath\Data\Schema\PDO;

use CPath\Autoloader;
use CPath\Build\Code\IConstructorArgs;
use CPath\Data\Schema\IReadableSchema;
use CPath\Data\Schema\IWritableSchema;

class PDOTableClassWriter implements IWritableSchema
{
	private $mPDOClass;
	private $mPath;
	private $mRes = null;
	private $mClassName;
	private $mPrimaryColumn;
	private $mAppend = null;
	private $mRowClass;

	public function __construct(\PDO $PDO, $className, $rowClass) {
		$this->mRowClass  = $rowClass;
		$this->mPDOClass  = get_class($PDO);
		$this->mClassName = str_replace('/', '\\', $className);
		$this->mPath      = Autoloader::getPathFromClassName($this->mClassName);
	}

	function __destruct() {
		if ($this->mRes)
			$this->finish();
	}

	function start(IReadableSchema $Schema) {
		if($this->mRes)
			throw new \InvalidArgumentException("Writer already started");

		$this->mRes      = fopen($this->mPath, 'w+');

		$Namespace       = ($ns = dirname($this->mClassName)) ? "\nnamespace " . $ns . ';' : '';
		$Use             = "\nuse " . AbstractPDOTable::cls() . ' as AbstractBase;';
		$Use            .= "\nuse " . $this->mPDOClass . ' as DB;';
		$Use            .= "\nuse " . get_class($Schema) . ';';
		if($Schema instanceof IConstructorArgs) {
			$Use        .= "\nuse " . IReadableSchema . ';';
		}
		//$Use            .= "\nuse " . IWritableSchema . ';';

		fwrite($this->mRes,
			<<<PHP
<?{$Namespace}
{$Use}

PHP
		);


	}

	function finish() {
		if (!$this->mRes)
			throw new \InvalidArgumentException("Writer already finished");


		fwrite($this->mRes, $this->mAppend . "\n}");
		$this->mAppend = null;

		fclose($this->mRes);
		$this->mRes = null;
	}

	/**
	 * Create a table in the schema
	 * @param IReadableSchema $Schema
	 * @param String $tableName
	 * @param String|null $tableArgs
	 * @param String|null $tableComment
	 * @return void
	 */
	function writeTable(IReadableSchema $Schema, $tableName, $tableArgs = null, $tableComment = null) {
		if (!$this->mRes)
			$this->start($Schema);

		$className = $this->mClassName;
		$baseClassName = basename($className);
		$ConstComment = $tableComment ? "\n\t * " . $tableComment : '';
		$Implements = null;
		if($Schema instanceof IConstructorArgs)
			$Implements = ' implements ' . basename(IReadableSchema);

		$rowClass = $this->mRowClass;

		fwrite($this->mRes,
			<<<PHP

/**
 * Class {$className}
 * @table {$tableName} {$tableArgs}{$ConstComment}
 */
class {$baseClassName} extends AbstractBase{$Implements} {
	const TABLE_NAME = '{$tableName}';
	const ROW_CLASS = '{$rowClass}';


PHP
		);

		if($Schema instanceof IConstructorArgs) {
			$schemaClassName = basename(get_class($Schema));
			$args = $Schema->getConstructorArgs();
			foreach($args as &$arg)
				$arg = var_export($arg, true);
			$construct = "new {$schemaClassName}(" . implode(', ', $args) . ')';

			$this->mAppend .= <<<PHP

	function getSchema() { return {$construct}; }
PHP;
		}

		$this->mAppend .= <<<PHP

	function getDatabase() { return new DB(); }
PHP;
	}


	/**
	 * Write a column to the last schema table
	 * @param IReadableSchema $Schema
	 * @param String $columnName
	 * @param String|null $columnArgs
	 * @param String|null $columnComment
	 * @return void
	 */
	function writeColumn(IReadableSchema $Schema, $columnName, $columnArgs = null, $columnComment = null) {
		$ConstName    = preg_replace('/[^\w_]/', '_', strtoupper($columnName));
		$ConstComment = $columnComment ? "\n\t// " . $columnComment : '';

		if(!$this->mPrimaryColumn && strpos($columnArgs, 'PRIMARY') !== false) {
			$this->mPrimaryColumn = $columnName;

			fwrite($this->mRes,
				<<<PHP
	// Primary Key Column
	const PRIMARY_COLUMN = '{$columnName}';

PHP
			);

		}

		fwrite($this->mRes,
			<<<PHP
	// @column {$columnName} {$columnArgs}{$ConstComment}
	const COLUMN_{$ConstName} = '{$columnName}';

PHP
		);
	}

	/**
	 * Write a column index to the last schema table
	 * @param IReadableSchema $Schema
	 * @param String $indexName
	 * @param String $columns list of columns comma delimited
	 * @param String|null $indexArgs
	 * @param String|null $indexComment
	 * @return mixed
	 */
	function writeIndex(IReadableSchema $Schema, $indexName, $columns, $indexArgs = null, $indexComment = null) {
		$ConstName    = preg_replace('/[^\w_]/', '_', strtoupper($indexName));
		$ConstComment = $indexComment ? "\n\t// " . $indexComment : '';

		fwrite($this->mRes,
			<<<PHP
	// @index {$indexName} {$indexArgs}{$ConstComment}
	const INDEX_{$ConstName} = '{$indexName}';

PHP
		);
	}
}