<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\Api\Field;
use CPath\Handlers\Api\Param;
use CPath\Handlers\Api\RequiredField;
use CPath\Handlers\Api\RequiredParam;
use CPath\Handlers\Api\Interfaces\IField;
use CPath\Interfaces\IAPI;
use CPath\Validate;

class PDOColumn {
    const BUILD_IGNORE = true;
    
    const FLAG_NUMERIC =  0x0001;
    const FLAG_ENUM =     0x0002;
    const FLAG_NULL =     0x0004;
    const FLAG_DEFAULT =  0x0008;

    const FLAG_INDEX =    0x0010;
    const FLAG_UNIQUE =   0x0020;
    const FLAG_PRIMARY =  0x0040;
    const FLAG_AUTOINC =  0x0080;

    const FLAG_REQUIRED = 0x0100;

    const FLAG_INSERT =   0x1000;
    const FLAG_UPDATE =   0x2000;
    const FLAG_SEARCH =   0x4000;
    const FLAG_EXPORT =   0x8000;

    protected
        $mName,
        $mComment,
        $mFlags,
        $mFilter,
        $mEnum;

    /**
     * Create a new Column
     * @param String $name the name
     * @param int $flags the flags
     * @param int $filter the default validation/filter
     * @param String $comment the comment
     * @param Array $enum the enum values
     */
    function __construct($name, $flags, $filter=0, $comment=NULL, $enum=NULL) {
        $this->mName = $name;
        $this->mFlags = $flags;
        $this->mFilter = $filter;
        $this->mComment = $comment;
        $this->mEnum = $enum;
    }

    /**
     * Returns the column name
     * @return String the column name
     */
    function getName() {
        return $this->mName;
    }

    /**
     * Returns true one or more flags are set, otherwise false
     * @param int $flag the flag or flags to check
     * @return bool true one or more flags are set, otherwise false
     */
    function isFlag($flag) {
        return $this->mFlags & $flag ? true : false;
    }

    /**
     * Get the comment for this column
     * @return String comment
     */
    function getComment() {
        return $this->mComment
            ?: $this->mComment = ucwords(str_replace('_', ' ', $this->mName));
    }

    /**
     * Set the comment for this column
     * @param String $comment
     * @return void
     */
    function setComment($comment) {
        $this->mComment = $comment;
    }

    /**
     * Validates an input with the validation config of this column
     * @param mixed $value the input to validate
     * @return mixed
     */
    function validate($value) {
        return Validate::inputField($this->mName, $value, $this->mFilter);
    }

    /**
     * Return an array of enum values for this Column
     * @return array enum values
     */
    function getEnumValues() {
        return $this->mEnum;
    }

    /**
     * Generate an IField for this column
     * @param boolean|NULL $required if null, the column flag FLAG_REQUIRED determines the value
     * @param boolean|NULL $param
     * @param boolean|NULL $comment
     * @param mixed $defaultValidation
     * @return IField
     */
    function generateField($required=NULL, $param=NULL, $comment=NULL, $defaultValidation=NULL) {
        if($required === NULL)
            $required = $this->mFlags & PDOColumn::FLAG_REQUIRED;
        if($this->mFilter)
            $defaultValidation = $this->mFilter;
        if($param) {
            if($required)
                $Field = new RequiredParam($comment ?: $this->getComment(), $defaultValidation);
            else
                $Field = new Param($comment ?: $this->getComment(),  $defaultValidation);
        } else {
            if($required)
                $Field = new RequiredField($comment ?: $this->getComment(), $defaultValidation);
            else
                $Field = new Field($comment ?: $this->getComment(),  $defaultValidation);
        }
        return $Field;
    }
}
