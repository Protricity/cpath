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

    private $mRequest;
    private $mFlags;
    private $mID;

    function __construct(IPrompt $Request, $flags = null) {
        $this->mRequest = $Request;
        $this->mFlags = $flags;
        $this->mID = ++self::$COUNT;
    }

    function getBuildID() {
        return $this->mID;
    }

    /**
     * Get or prompt for a value from the request.
     * @param string|IDescribable $description description for this prompt
     * @param string|int|null $key [optional] the parameter key or index
     * @param string|null $defaultValue [optional] default value if prompt fails
     * @return mixed the parameter value
     * @throws \CPath\Request\Exceptions\RequestParameterException if a prompt failed to produce a result
     * Example:
     * $name = $Request->prompt('Please enter your name');          // Gets name from next arg if available
     * $name = $Request->prompt('Please enter your name', 'name');  // Gets name from ['name'] if set
     */
    function prompt($description, $key = null, $defaultValue = null) {
        return $this->mRequest->prompt($description, $key, $defaultValue);
    }

    /**
     * Return the flags for this request
     * @return int
     */
    function hasFlag() {
        return $this->mFlags;
    }
}