<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/5/14
 * Time: 8:23 PM
 */
namespace CPath\Data\Ledger\File;

class LineReader
{
	const BUFFER_SIZE = 4096;
	const SEPARATOR   = "\n";

	private $mPos = 0;
	private $mLine = 0;
	private $mHandle;
	private $mFileSize;

	public function __construct($filename) {
		$this->mHandle   = fopen($filename, 'r');
		if(!$this->mHandle)
			throw new \InvalidArgumentException("Invalid File: " . $filename);
		$this->mFileSize = filesize($filename);
	}

	public function __destruct() {
		fclose($this->mHandle);
	}

	public function readLine() {
		$line = fgets($this->mHandle, self::BUFFER_SIZE);
		if ($line === false)
			return $line;

		$this->mPos += sizeof($line);
		$this->mLine += 1;

		return $line;
	}
}
