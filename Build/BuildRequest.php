<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/10/14
 * Time: 11:12 PM
 */
namespace CPath\Build;

use CPath\Request\Exceptions\RequestParameterException;
use CPath\Request\IPromptRequest;

class BuildRequest implements IBuildRequest
{
    private static $COUNT = 1;

    private $mRequest;
    private $mFlags;
    private $mID;

    function __construct(IPromptRequest $Request, $flags = null) {
        $this->mRequest = $Request;
        $this->mFlags = $flags;
        $this->mID = ++self::$COUNT;
    }

    function getBuildID() {
        return $this->mID;
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
        return $this->mRequest->prompt($name, $description, $defaultValue);
    }

    /**
     * Return the flags for this request
     * @return int
     */
    function hasFlag() {
        return $this->mFlags;
    }
}