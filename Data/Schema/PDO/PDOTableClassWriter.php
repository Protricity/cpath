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
	private $mClassName;
	private $mFetchClass;


	private $mTableInfo = null;
	private $mColumns = array();
	private $mIndexes = array();

	public function __construct(\PDO $PDO, $className, $rowClass) {
		$this->mFetchClass  = $rowClass;
		$this->mPDOClass  = get_class($PDO);
		$this->mClassName = str_replace('/', '\\', $className);
		$this->mPath      = Autoloader::getPathFromClassName($this->mClassName);
	}

	function __destruct() {
		$this->commit();
	}


	public function commit() {
		if ($this->mTableInfo !== null) {
			list($Schema, $tableName, $tableArgs, $oldTableComment) = $this->mTableInfo;
			$this->mTableInfo = null;

			$abstractBaseClass = AbstractPDOTable::className;
			$fetchClass = $this->mFetchClass;
			$fetchClassBaseName = basename($fetchClass);

			$constList = array(
				'TABLE_NAME' => $tableName,
				'FETCH_CLASS' => $fetchClass,
				'SELECT_COLUMNS' => null,
				'UPDATE_COLUMNS' => null,
				'INSERT_COLUMNS' => null,
			);

			$primaryKeyColumn = null;
			foreach($this->mColumns as $columnInfo) {
				list($columnName, $columnArgs, $columnComment) = $columnInfo;
				if (!$primaryKeyColumn && strpos($columnArgs, 'PRIMARY') !== false)
					$primaryKeyColumn = $columnName;
				if(preg_match_all('/^\s+\*\s+@(select|update|insert)\s(\w+)?/m', $columnComment, $matches)) {
					foreach($matches[1] as $i => $match) {
						$key = strtoupper($match) . '_COLUMNS';
						$c = $constList[$key];
						$constList[$key] = ($c ? $c . ', ' : '') . ($matches[2][$i] ? $matches[2][$i] : $columnName);
					}
				}
			}

			if($primaryKeyColumn) {
				$constList['PRIMARY_COLUMN'] = $primaryKeyColumn;
				$abstractBaseClass           = AbstractPDOPrimaryKeyTable::className;
			}

			$compareDefault = "'=?'";
			$searchColumnDefault = $primaryKeyColumn ? "'{$primaryKeyColumn}'" : "null";

			$fRes            = fopen($this->mPath, 'w+');

			$namespace       = ($ns = dirname($this->mClassName)) ? "\nnamespace " . $ns . ';' : '';
			$useList         = "\nuse " . $abstractBaseClass . ' as AbstractBase;';
			$useList        .= "\nuse " . $this->mPDOClass . ' as DB;';
			$useList        .= "\nuse " . get_class($Schema) . ';';
			if($Schema instanceof IConstructorArgs) {
				$useList    .= "\nuse " . IReadableSchema::interfaceName . ';';
			}
			//$Use            .= "\nuse " . IWritableSchema . ';';

			$className = $this->mClassName;
			$baseClassName = basename($className);
//			$constComment = $tableComment ? "\n\t * " . $tableComment : '';
			$Implements = null;
			if($Schema instanceof IConstructorArgs)
				$Implements = ' implements ' . basename(IReadableSchema);

			if(strpos($oldTableComment, '/**') === false) {
			}

			$tableComment = "";
			if(preg_match_all('/^\s+\*\s+@(\w+.*)$/m', $oldTableComment, $matches)) {
				foreach($matches[1] as $i => $comment)
					$tableComment .= "\n * @" . rtrim($comment);
			}

			// write

			fwrite($fRes,
				<<<PHP
<?{$namespace}
{$useList}

/**
 * Class {$baseClassName}{$tableComment}
 * @method {$fetchClassBaseName} insertOrUpdate(\$primaryKeyValue, Array \$insertData) insert or update a {$fetchClassBaseName} instance
 * @method {$fetchClassBaseName} insertAndFetch(Array \$insertData) insert and fetch a {$fetchClassBaseName} instance
 * @method {$fetchClassBaseName} fetch(\$whereColumn, \$whereValue=null, \$compare={$compareDefault}, \$selectColumns=null) fetch a {$fetchClassBaseName} instance
 * @method {$fetchClassBaseName} fetchOne(\$whereColumn, \$whereValue=null, \$compare={$compareDefault}, \$selectColumns=null) fetch a single {$fetchClassBaseName}
 * @method {$fetchClassBaseName}[] fetchAll(\$whereColumn, \$whereValue=null, \$compare={$compareDefault}, \$selectColumns=null) fetch an array of {$fetchClassBaseName}[]
 */
class {$baseClassName} extends AbstractBase{$Implements} {
PHP
			);

			foreach($constList as $constName => $constValue)
				if($constValue)
					fwrite($fRes, "\n\tconst {$constName} = " . var_export($constValue, true) . ';');

			foreach($this->mColumns as $columnInfo) {
				list($columnName, $columnArgs, $columnComment) = $columnInfo;

				$comment = "\n";
				if(preg_match_all('/^\s+\*\s+@(\w+.*)$/m', $columnComment, $matches))
					foreach($matches[1] as $i => $c)
						$comment .= "\n\t * @" . rtrim($c);

				$constName    = preg_replace('/[^\w_]/', '_', strtoupper($columnName));
				if($comment)
					fwrite($fRes, "\n\t/**{$comment}\n\t */");
				fwrite($fRes, "\n\tconst COLUMN_{$constName} = " . var_export($columnName, true) . ';');
			}

			foreach($this->mIndexes as $indexInfo) {
				list($indexName, $columns, $indexArgs, $indexComment) = $indexInfo;
				$comment = "\n\n\t * @index " . $indexArgs;
				$comment .= "\n\t * @columns " . $columns;
//				if(preg_match_all('/^\s+\*\s+@(index\w*).*$/m', $indexComment, $matches))
//					foreach($matches[1] as $i => $c)
//						$comment .= "\n\t * @" . rtrim($c);

				$constName    = preg_replace('/[^\w_]/', '_', strtoupper($indexName));
				if($comment)
					fwrite($fRes, "\n\t/**{$comment}\n\t */");
				fwrite($fRes, "\n\tconst {$constName} = " . var_export($indexName, true) . ';');
			}

//			if($primaryKeyColumn && $constList['UPDATE_COLUMNS']) {
//				$args = '$' . implode(' = null, $', explode(', ', $constList['UPDATE_COLUMNS'])) . ' = null';
//				fwrite($fRes, "\n\n\tfunction updateFields({$args}, \$where) { \$this->update(get_defined_vars(), \$where); }");
//			}

			if($constList['INSERT_COLUMNS']) {
				$args = '$' . implode(' = null, $', explode(', ', $constList['INSERT_COLUMNS'])) . ' = null';
				fwrite($fRes, "\n\n\tfunction insertRow({$args}) { \n\t\treturn \$this->insert(get_defined_vars());\n\t}");
			}

//			if($primaryKeyColumn) {
//				fwrite($fRes, "\n\n\tfunction delete({$fetchClassBaseName} \$Row) { return parent::deleteAt(\$Row->{$primaryKeyColumn}); }");
//			}

			if($Schema instanceof IConstructorArgs) {
				$schemaClassName = basename(get_class($Schema));
				$args = $Schema->getConstructorArgs();
				foreach($args as &$arg)
					$arg = var_export($arg, true);
				$construct = "new {$schemaClassName}(" . implode(', ', $args) . ')';

				fwrite($fRes, "\n\n\tfunction getSchema() { return {$construct}; }");
			}

			fwrite($fRes, "\n\n\tprivate \$mDB = null;\n\tfunction getDatabase() { return \$this->mDB ?: \$this->mDB = new DB(); }");

			fwrite($fRes, "\n}");

			fclose($fRes);
		}
		$this->mTableInfo = null;
		$this->mColumns   = array();
		$this->mIndexes   = array();
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
		$this->commit();
		$this->mTableInfo = func_get_args();
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
		$this->mColumns[] = array_slice(func_get_args(), 1);
	}

	/**
	 * Write a column index to the last schema table
	 * @param IReadableSchema $Schema
	 * @param $indexName
	 * @param String $columns list of columns comma delimited
	 * @param String|null $indexArgs
	 * @param String|null $indexComment
	 * @return mixed
	 */
	function writeIndex(IReadableSchema $Schema, $indexName, $columns, $indexArgs = null, $indexComment = null) {
		$this->mIndexes[] = array_slice(func_get_args(), 1);
	}
}