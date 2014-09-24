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
use CPath\Request\Log\ILogListener;
use CPath\Request\MimeType\IRequestedMimeType;
use CPath\Request\MimeType;
use CPath\Request\Request;

class CLIRequest extends Request implements IPrompt
{
    /** @var ILogListener[] */
    private $mLogs = array();
    private $mPos = 0;
    /** @var IRequestedMimeType */
    private $mMimeType=null;

    public function __construct($path = null, Array $args = null, $logFlags=0) {
        $this->mFlags = $logFlags;

        if ($args === null) {
            $args = $_SERVER['argv'];
            $file = array_shift($args);
            $args = CommandString::parseArgs($args);
        }
        if (isset($args[0]) && !$path) {
            $path = $args[0];
            $this->mPos++;
        }

        $flags = 0;
        if(isset($args['v']) || isset($args['verbose']))
            $flags |= ILogListener::VERBOSE;

        $this->mMimeType = new TextMimeType($flags);

        parent::__construct($path, $args);

    }

    /**
     * Get the Request Method (CLI)
     * @return String
     */
    function getMethodName() {
        return 'CLI';
    }

    /**
     * Get the requested Mime types for rendering purposes
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeType() {
        return $this->mMimeType;
    }

    /**
     * Set the requested Mime type for this request
     * @param MimeType\IRequestedMimeType $MimeType
     * @return void
     */
    function setMimeType(IRequestedMimeType $MimeType) {
        $this->mMimeType = $MimeType;
    }

    function getNextArg() {
        if($this->hasValue($this->mPos))
            $this->getValue($this->mPos++);
        return null;
    }

    /**
     * Prompt for a value from the request.
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @return mixed the parameter value or null on failure
     * Example:
     * $name = $Request->promptField('name', 'Please enter your name', 'MyName');  // Gets value for parameter 'name' or returns default string 'MyName'
     */
    function prompt($description = null) {
        if($arg = $this->getNextArg())
            return $arg;

        if (PHP_OS == 'WINNT') {
            echo $description;
            $line = stream_get_line(STDIN, 0, "\n");
        } else {
            $line = readline($description);
        }
        return $line;
    }
}