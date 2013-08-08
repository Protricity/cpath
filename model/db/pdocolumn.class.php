<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\APIField;
use CPath\Handlers\APIRequiredField;
use CPath\Interfaces\IAPI;
use CPath\Validate;

class PDOColumn {
    const Build_Ignore = true;
    
    const FlagNumeric =  0x0001;
    const FlagEnum =     0x0002;
    const FlagNull =     0x0004;

    const FlagIndex =    0x0010;
    const FlagUnique =   0x0020;
    const FlagPrimary =  0x0040;
    const FlagAutoInc =  0x0080;

    const FlagRequired = 0x0100;

    const FlagInsert =   0x1000;
    const FlagUpdate =   0x2000;
    const FlagSearch =   0x4000;
    const FlagExport =   0x8000;

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
            ?: $this->mComment = ucwords(str_replace('_', ' ', $this->mComment));
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
     * Add this column to an IAPI as a field
     * @param boolean|NULL $required if null, the column flag FlagRequired determines the value
     * @param IAPI $API
     */
    function addToAPI(IAPI $API, $required=NULL) {
        if($required === NULL)
            $required = $this->mFlags & PDOColumn::FlagRequired;
        if($required)
            $API->addField($this->mName, new APIRequiredField($this->getComment(), $this->mFilter));
        else
            $API->addField($this->mName, new APIField($this->getComment(),  $this->mFilter));
    }
}
