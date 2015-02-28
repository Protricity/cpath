<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/21/2014
 * Time: 9:00 PM
 */
namespace CPath\Data\Schema\PDO;


use CPath\Data\Schema\IReadableSchema;
use CPath\Request\IRequest;
use CPath\Request\Log\ILogListener;

abstract class AbstractPDOQueryBuilder implements ILogListener
{
	private $mPDO;
	private $mValues = array();
	private $mModes = array();
	/** @var \PDOStatement */
	private $mStatement = null;

	/** @var ILogListener[] */
	private $mLogListeners = array();

	protected $mExecuted = false;
	protected $mValueIndex = 1;
	protected $mTableSQL = null;
	protected $mFormat = null;

	abstract protected function getSQL();

	public function __construct(\PDO $PDO) {
		$this->mPDO = $PDO;
	}

	public function __destruct() {
		if($this->mStatement === null) {
			$this->execute();
		}
	}

	public function getDatabase() {
		return $this->mPDO;
	}

	public function table($table, $alias = null) {
		if ($alias !== null) {
			$table .= " as " . $alias;
		}

		$this->mTableSQL = $table;

		return $this;
	}

	public function setFetchMode($mode) {
		$this->mModes[] = func_get_args();

		return $this;
	}

	public function getValues() {
		return $this->mValues;
	}

	public function bindValue($value, $name=null) {
		if($name === null)
			$this->mValues[$this->mValueIndex++] = $value;
		else
			$this->mValues[$name] = $value;

		return $this;
	}

	public function prepare(IRequest $Request = null) {
		if($Request)
			$this->addLogListener($Request);

		if($this->mStatement)
			return $this->mStatement;

		$DB = $this->getDatabase();
		$sql       = $this->getSQL();

		try {
			$statement = $DB->prepare($sql);
			$this->log($sql, $this::VERBOSE);

		} catch (\PDOException $ex) {
			$statement = null;
			if (preg_match('/(duplicate|unique)/i', $ex->getMessage(), $matches))
				throw new PDODuplicateRowException($this, $ex->getMessage(), null, $ex);

			if($this->tryRepairTable($ex)) {
				$statement = $DB->prepare($sql);
				$this->log($sql, $this::VERBOSE);

			} else {
				throw new \PDOException($ex->getMessage() . ' - ' . $this->getSQL(), intval($ex->getCode()), $ex);
			}
		}

		foreach ($this->mModes as $mode)
			if(isset($mode[1]))
				$statement->setFetchMode($mode[0], $mode[1], isset($mode[2]) ? $mode[2] : null);
			else
				$statement->setFetchMode($mode[0]);

		foreach ($this->mValues as $k => $v)
			$statement->bindValue($k, $v);

		return $this->mStatement = $statement;
	}

	public function execute(IRequest $Request = null, Array $values = null) {
		if($Request)
			$this->addLogListener($Request);

		try {
			$statement = $this->prepare($Request);
			if(!$this->mExecuted) {
				$this->mExecuted = $statement->execute($values);
				$this->mValues = array();
			}
		} catch (\PDOException $ex) {
			if (preg_match('/duplicate/i', $ex->getMessage()))
				throw new PDODuplicateRowException($this, $ex->getMessage(), null, $ex);

			if($this->tryRepairTable($ex)) {
				$statement = $this->prepare($Request);
				$this->mExecuted = $statement->execute($values);
				$this->mValues = array();

			} else {
				throw new \PDOException($ex->getMessage() . ' - ' . $this->getSQL(), intval($ex->getCode()), $ex);
			}
		}
		return $this->mExecuted;
	}

	public function rowCount() {
		if($this->mStatement)
			return $this->mStatement->rowCount();
		return null;
	}

	public function format($format) {
		$this->mFormat = $format;
	}

	protected function tryRepairTable(\PDOException $ex) {
		if (preg_match('/(column|table)( or view)? not found/i', $ex->getMessage(), $matches)) {
			$DB = $this->getDatabase();
			$Schema = $DB;
			if($Schema instanceof IReadableSchema) {
				$TableWriter = new PDOTableWriter($DB);
				$repaired = $TableWriter->tryRepair($ex, $Schema);
				if($repaired)
					return true;
			}
		}
		return false;
	}

	/**
	 * Add a log entry
	 * @param mixed $msg The log message
	 * @param int $flags [optional] log flags
	 * @return int the number of listeners that processed the log entry
	 */
	function log($msg, $flags = 0) {
		$c = 0;
		foreach($this->mLogListeners as $Log)
			$c += $Log->log($msg, $flags);
		return $c;
	}

	/**
	 * Add a log listener callback
	 * @param ILogListener $Listener
	 * @return void
	 * @throws \InvalidArgumentException if this log listener inst does not accept additional listeners
	 */
	function addLogListener(ILogListener $Listener) {
		if(!in_array($Listener, $this->mLogListeners))
			$this->mLogListeners[] = $Listener;
	}
}
