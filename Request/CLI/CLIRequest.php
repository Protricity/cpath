<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/21/14
 * Time: 3:18 PM
 */
namespace CPath\Request\CLI;

use CPath\Describable\IDescribable;
use CPath\Render\Text\TextMimeType;
use CPath\Request\Executable\IPrompt;
use CPath\Request\Form\IFormRequest;
use CPath\Request\Log\ILogListener;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\Parameter\IRequestParameter;
use CPath\Request\Parameter\Parameter;
use CPath\Request\Request;

class CLIRequest extends Request implements IPrompt, IFormRequest
{

    public function __construct($path = null, $args = null, $logFlags=0, IRequestedMimeType $MimeType=null) {
        $this->mFlags = $logFlags;

        if ($args === null) {
            $args = $_SERVER['argv'];
            $file = array_shift($args);
            $args = CommandString::parseArgs($args);
        }
        if (isset($args[0]) && !$path) {
            $path = array_shift($args);
        }

        $flags = 0;
        if(isset($args['v']) || isset($args['verbose']))
            $flags |= ILogListener::VERBOSE;

        parent::__construct('CLI', $path, $args, $MimeType ?: new TextMimeType($flags));

    }


    /**
     * Prompt for a value from the request.
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @return mixed the parameter value or null on failure
     * Example:
     * $name = $Request->promptField('name', 'Please enter your name', 'MyName');  // Gets value for parameter 'name' or returns default string 'MyName'
     */
    function prompt($description = null) {
//        if($arg = $this->getNextArgument())
//            return $arg;

        if (PHP_OS == 'WINNT') {
            echo $description;
            $line = stream_get_line(STDIN, 0, "\n");
        } else {
            $line = readline($description);
        }
        return $line;
    }

	/**
	 * Return a request value
	 * @param $fieldName
	 * @return mixed the form field value
	 */
	function getFormFieldValue($fieldName) {
		return $this->getArgumentValue($fieldName);
	}

//	/**
//	 * Return a request value
//	 * @param String|IRequestParameter $Parameter string or instance
//	 * @internal param null|String $description
//	 * @return mixed the validated parameter value
//	 */
//	function getValue(IRequestParameter $Parameter) {
//		$this->addParam($Parameter);
//
//		$paramName = $Parameter->getName();
//		if($this->hasArgumentValue($paramName)) {
//			$value = $this->getArgumentValue($paramName);
//		}
//
//		if(!$value) {
//			$msg = "[--{$paramName}] " . $description . ": ";
//			if($value = $this->prompt($msg)) {
//				$value = $Parameter->validateParameter($this, $value);
//			}
//
//		}
//
//		return $Parameter->validateParameter($this, $value);
//	}
}