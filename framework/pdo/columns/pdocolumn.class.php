<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Columns;


use Aws\ElasticTranscoder\Exception\ValidationException;
use CPath\Describable\IDescribable;
use CPath\Handlers\Api\EnumField;
use CPath\Handlers\Api\Field;
use CPath\Handlers\Api\Interfaces\IField;
use CPath\Handlers\Api\PasswordField;
use CPath\Interfaces\IRequest;
use CPath\Validate;

class PDOColumn implements IDescribable {
    const BUILD_IGNORE = true;
    
    const FLAG_NUMERIC =  0x000001;
    const FLAG_ENUM =     0x000002;
    const FLAG_NULL =     0x000004;
    const FLAG_DEFAULT =  0x000008;

    const FLAG_INDEX =    0x000010;
    const FLAG_UNIQUE =   0x000020;
    const FLAG_PRIMARY =  0x000040;
    const FLAG_AUTOINC =  0x000080;

    const FLAG_REQUIRED = 0x000100;
    const FLAG_OPTIONAL = 0x000200;

    const FLAG_INSERT =   0x001000;
    const FLAG_UPDATE =   0x002000;
    const FLAG_SEARCH =   0x004000;
    const FLAG_EXPORT =   0x008000;

    const FLAG_PASSWORD = 0x010000;

    protected
        $mName,
        $mComment,
        $mFlags,
        $mFilter,
        $mDefault,
        $mEnum;

    /**
     * Create a new Column
     * @param String $name the name
     * @param int $flags the flags
     * @param int $filter the default validation/filter
     * @param String $comment the comment
     * @param String $default the default value
     * @param Array $enum the enum values
     */
    function __construct($name, $flags, $filter=0, $comment=NULL, $default=NULL, $enum=NULL) {
        $this->mName = $name;
        $this->mFlags = $flags;
        $this->mFilter = $filter;
        $this->mComment = $comment;
        $this->mDefault = $default;
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
     * Note: multiple flags follows 'OR' logic. Only one flag has to match to return true
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

    function hasDefaultValue() {
        return $this->mDefault ? true : false;
    }

    /**
     * Generate default value for this
     */
    function getDefaultValue() {
        if(!$this->mDefault)
            return NULL;
//        switch(strtolower($this->mDefault)) {
//            case 'time()': return time();
//            case 'uniqid()': return uniqid();
//        }
//        throw new \Exception("Invalid Default value: " . $this->mDefault);
        $eval = trim($this->mDefault);
        return eval('return ' . $eval . ';');
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
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return mixed the formatted input field that passed validation
     * @throws ValidationException if validation fails
     */
    function validate(IRequest $Request, $fieldName) {
        $value = $Request[$fieldName];
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
    function generateAPIField($required=NULL, $param=NULL, $comment=NULL, $defaultValidation=NULL) {
        if($required === NULL)
            $required = ($this->mFlags & PDOColumn::FLAG_REQUIRED) && !($this->mFlags & PDOColumn::FLAG_OPTIONAL);
        if($this->mFilter)
            $defaultValidation = $this->mFilter;

        if($this->mEnum)
            $Field = new EnumField($comment ?: $this->getComment(), $this->mEnum, $required, $param);
        elseif($this->isFlag(self::FLAG_PASSWORD))
            $Field = new PasswordField($comment ?: $this->getComment(), $defaultValidation, $required, $param);
        else
            $Field = new Field($comment ?: $this->getComment(), $defaultValidation, $required, $param);

        if($this->hasDefaultValue())
            $Field->setDefaultValue($this->getDefaultValue());
        return $Field;
    }


    /**
     * Get a simple public-visible title of this object as it would be displayed in a header (i.e. "Mr. Root")
     * @return String title for this Object
     */
    function getTitle() {
        $words = explode('_', $this->mName);
        foreach($words as &$word)
            if(strlen($word) <= 2)
                $word = strtoupper($word);
        return ucwords(implode(' ', $words));
    }

    /**
     * Get a simple public-visible description of this object as it would appear in a paragraph (i.e. "User account 'root' with ID 1234")
     * @return String simple description for this Object
     */
    function getDescription() {
        return $this->getComment();
    }

    /**
     * Get a simple world-visible description of this object as it would be used when cast to a String (i.e. "root", 1234)
     * Note: This method typically contains "return $this->getTitle();"
     * @return String simple description for this Object
     */
    function __toString() {
        return $this->mName;
    }
}
