<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/5/14
 * Time: 11:11 PM
 */
namespace CPath\Data\Ledger\JSON;

use CPath\Data\Ledger\File\LineReader;

class JSONLineReader extends LineReader
{
	public function readJSON() {
		$line = $this->readLine();
		if (!$line)
			return $line;

		return json_decode($line, true);
	}
}