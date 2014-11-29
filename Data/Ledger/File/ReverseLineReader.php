<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/5/14
 * Time: 8:13 PM
 */
namespace CPath\Data\Ledger\File;

class ReverseLineReader implements \Iterator
{
	const BUFFER_SIZE = 4096;
	const SEPARATOR   = "\n";

	private $mPos;
	private $mHandle;
	private $mFileSize;
	private $mBuffer;
	private $mKey;
	private $mValue;

	public function __construct($filename) {
		$this->mHandle   = fopen($filename, 'r');
		$this->mFileSize = filesize($filename);
		$this->mPos      = -1;
		$this->mBuffer   = null;
		$this->mKey      = -1;
		$this->mValue    = null;
	}

	public function read($size) {
		$this->mPos -= $size;
		fseek($this->mHandle, $this->mPos);

		return fread($this->mHandle, $size);
	}

	public function readline() {
		$buffer =& $this->mBuffer;
		while (true) {
			if ($this->mPos == 0) {
				return array_pop($buffer);
			}
			if (count($buffer) > 1) {
				return array_pop($buffer);
			}
			$buffer = explode(self::SEPARATOR, $this->read(self::BUFFER_SIZE) . $buffer[0]);
		}

		return null;
	}

	public function next() {
		++$this->mKey;
		$this->mValue = $this->readline();
	}

	public function rewind() {
		if ($this->mFileSize > 0) {
			$this->mPos    = $this->mFileSize;
			$this->mValue  = null;
			$this->mKey    = -1;
			$this->mBuffer = explode(self::SEPARATOR, $this->read($this->mFileSize % self::BUFFER_SIZE ? : self::BUFFER_SIZE));
			$this->next();
		}
	}

	public function key() { return $this->mKey; }

	public function current() { return $this->mValue; }

	public function valid() { return !is_null($this->mValue); }
}

