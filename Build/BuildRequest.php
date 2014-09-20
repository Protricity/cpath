<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 11:12 PM
 */
namespace CPath\Build;

use CPath\Describable\IDescribable;
use CPath\Request\Exceptions\RequestParameterException;
use CPath\Request\Executable\IPrompt;

class BuildRequest implements IBuildRequest
{
    private static $COUNT = 1;

    private $mPrompt;
    private $mFlags;
    private $mID;

    function __construct(IPrompt $Prompt, $flags = null) {
        $this->mPrompt = $Prompt;
        $this->mFlags = $flags;
        $this->mID = ++self::$COUNT;
    }

    function getBuildID() {
        return $this->mID;
    }

    /**
     * Prompt for a value from the request.
     * @param string $name the parameter name
     * @param string|IDescribable|null $description [optional] description for this prompt
     * @param string|null $defaultValue [optional] default value if prompt fails
     * @return mixed the parameter value
     * @throws \CPath\Request\Exceptions\RequestParameterException if a prompt failed to produce a result
     * Example:
     * $name = $Request->promptField('name', 'Please enter your name', 'MyName');  // Gets value for parameter 'name' or returns default string 'MyName'
     */
    function prompt($name, $description = null, $defaultValue = null) {
        return $this->mPrompt->prompt($name, $description, $defaultValue);
    }

    /**
     * Test values for one or more flags
     * @param String $_flag vararg of flags.
     * ->hasFlag(FLAG1 | FLAG2, FLAG3) returns true IF (either FLAG1 OR FLAG2 is set) AND (FLAG3 is set)
     * @return bool
     */
    function hasFlag($_flag) {
        foreach(func_get_args() as $arg)
            if(!($arg & $this->mFlags))
                return false;

        return true;
    }
}