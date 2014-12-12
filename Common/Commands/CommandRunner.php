<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 12/10/2014
 * Time: 12:29 PM
 */
namespace CPath\Common\Commands;


use CPath\Request\Executable\IExecutable;
use CPath\Request\IRequest;
use CPath\Response\IResponse;

class CommandRunner implements IExecutable, IResponse
{

	/**
	 * Execute a command and return a response. Does not render
	 * @param IRequest $Request
	 * @return IResponse the execution response
	 */
	function execute(IRequest $Request) {
		$cmd = $this->getCommand(false);

		$descriptors = array(
			0 => array("pipe", "r"), // stdin is a pipe that the child will read from
			1 => array("pipe", "w"), // stdout is a pipe that the child will write to
			2 => array("pipe", "w"), // stderr is a pipe that the child will write to
			//2 => array("file", "error-output.txt", "a") // stderr is a file to write to
		);

		$pipes = array();
		$this->log("$" . $this->getCommand(true));
		$process = proc_open($cmd, $descriptors, $pipes);

		if (!is_resource($process)) {
			$this->mRequest = null;
			throw new \Exception("Command failed: " . $cmd . "\n" . print_r(error_get_last(), true));
		}

		if ($stdOut)
			fwrite($pipes[0], $stdOut);


		stream_set_timeout($pipes[1], 5);
		$output = '';
		while (($line = fgets($pipes[1], 4096)) !== false) {
			if (self::RETURN_OUTPUT)
				$output .= $line;
			$line = rtrim($line);
			$this->log($line, static::VERBOSE);
			$ret = $this->onOutputLine($line);
			if ($ret)
				break;
		}
		// $output = stream_get_contents($pipes[1]); // TODO: fgets

		fclose($pipes[0]);
		fclose($pipes[1]);

		stream_set_blocking($pipes[2], 0);
		stream_set_timeout($pipes[2], 5);
		$stdErr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		$returnValue = proc_close($process);
	}
}