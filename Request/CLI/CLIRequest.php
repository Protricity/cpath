<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/7/14
 * Time: 12:45 PM
 */
namespace CPath\Request\CLI;

use CPath\Request\IRequest;
use CPath\Request\MimeType\TextMimeType;
use CPath\Request\Exceptions\RequestParameterException;

final class CLIRequest implements IRequest
{
    private $mPath;
    private $mCommandLine;

    public function __construct(Array $argv) {
        array_unshift($argv, $path);
        $this->mCommandLine = new CommandString($argv);
        $this->mPath = $path;
    }

    /**
     * Get the requested Mime types
     * @return \CPath\Request\MimeType\IRequestedMimeType[]
     */
    function getMimeTypes() {
        static $types = null;
        return $types ?: $types
            = array(new TextMimeType('text/plain'));
    }

    /**
     * Get the Request Method (GET, POST, PUT, PATCH, DELETE, or CLI)
     * @return String
     */
    function getMethodName() {
        return 'CLI';
    }

    /**
     * Get the route path
     * @return String the route path starting with '/'
     */
    function getPath() {
        return $this->mPath;
    }

    /**
     * Return the next non-parameter argument in sequence or null if none remain
     * @return String|null
     */
    function getNextArg() {
        return $this->mCommandLine->getNextArg();
    }

    /**
     * Get a parameter value by name
     * @param string $name the parameter name
     * @param string|null $description optional description for this parameter
     * @param string|null $defaultValue optional default value if prompt fails
     * @return string the parameter value
     * @throws RequestParameterException if a prompt failed to produce a result
     */
    function prompt($name, $description = null, $defaultValue = null) {
        if($this->mCommandLine->hasOption($name))
            return $this->mCommandLine->hasOption($name);

        $line = readline($description); // readline_add_history
        if($line)
            return $line;

        if($defaultValue !== null)
            return $defaultValue;

        throw new RequestParameterException("GET parameter '" . $name . "' not set", $name, $description);
    }
}