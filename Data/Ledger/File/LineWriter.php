<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/5/14
 * Time: 8:47 PM
 */
namespace CPath\Data\Ledger\File;

class LineWriter
{
	const SEPARATOR   = "\n";

	private $mHandle;
	private $mFileSize;

	public function __construct($filename, $mode='a+') {
		$this->mHandle   = fopen($filename, $mode);
		$this->mFileSize = filesize($filename);
	}

	public function writeLine($line) {
		return fputs($this->mHandle, $line . self::SEPARATOR);
	}
}