<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/28/14
 * Time: 7:47 PM
 */
namespace CPath\Request;

class RequestParameter
{
    const FLAG_REQUIRED = 0x01;
    const FLAG_ERROR = 0x02;

    private $mParamName;
    private $mValue;
    private $mDescription;
    private $mFlags;
    private $mError = null;

    public function __construct($value, $paramName, $description = null, $flags = 0)
    {
        $this->mValue = $value;
        $this->mParamName = $paramName;
        $this->mDescription = $description;
        $this->mFlags = $flags;
    }

    /**
     * Get Parameter Name
     * @return mixed
     */
    function getName() {
        return $this->mParamName;
    }

    /**
     * Get Parameter Name
     * @return mixed
     */
    function getValue() {
        return $this->mValue;
    }

    /**
     * Get Parameter Description
     * @return mixed
     */
    function getDescription() {
        return $this->mValue;
    }

    /**
     * Returns true if the parameter is required, otherwise false
     * @return bool
     */
    function isRequired() {
        return $this->hasFlag(self::FLAG_REQUIRED);
    }

    /**
     * Returns true if the parameter is required, otherwise false
     * @param String $err
     * @return bool
     */
    function setError($err) {
        $this->mError = $err;
        $this->mFlags |= self::FLAG_ERROR;
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

    /**
     * Return all flags as an integer
     * @return int
     */
    function getFlags() {
        return $this->mFlags;
    }
}