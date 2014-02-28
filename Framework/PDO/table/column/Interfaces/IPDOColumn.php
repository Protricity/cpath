<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\Table\Column\Interfaces;

use CPath\Framework\Api\Exceptions\ValidationException;
use CPath\Framework\Api\Field\Interfaces\IField;
use CPath\Framework\Data\Collection\ICollectionItem;
use CPath\Framework\Request\Interfaces\IRequest;

interface IPDOColumn extends ICollectionItem {

    const FLAG_NUMERIC =  0x000001;
    const FLAG_ENUM =     0x000002;
    const FLAG_NULL =     0x000004;
    const FLAG_DEFAULT =  0x000008;

    const FLAG_INDEX =    0x000010;
    const FLAG_UNIQUE =   0x000020;
    const FLAG_PRIMARY =  0x000040;
    const FLAG_AUTOINC =  0x000080;

    const FLAG_REQUIRED = 0x000100;
    //const FLAG_OPTIONAL = 0x000200;

    const FLAG_INSERT =   0x001000;
    const FLAG_UPDATE =   0x002000;
    const FLAG_SEARCH =   0x004000;
    const FLAG_EXPORT =   0x008000;

    const FLAG_PASSWORD = 0x010000;

    /**
     * Returns the column name
     * @return String the column name
     */
    function getName();

    /**
     * Returns true one or more flags are set, otherwise false
     * Note: multiple flags follows 'OR' logic. Only one flag has to match to return true
     * @param int $flag the flag or flags to check
     * @return bool true one or more flags are set, otherwise false
     */
    function hasFlag($flag);

    /**
     * Get the comment for this column
     * @return String comment
     */
    function getMComment();

    /**
     * Returns the default value or null if no default value is set
     * @return mixed|null
     */
    function getDefaultValue();

    /**
     * Generate an IField for this column
     * @param boolean|NULL $comment
     * @param mixed $defaultValidation
     * @param int $flags optional IField:: flags
     * @internal param bool|NULL $required if null, the column flag FLAG_REQUIRED determines the value
     * @internal param bool|NULL $param
     * @return IField
     */
    // TODO: refactor out
    function generateAPIField($comment=NULL, $defaultValidation=NULL, $flags=0);

    /**
     * Validates an input field. Throws a ValidationException if it fails to validate
     * @param IRequest $Request the request instance
     * @param String $fieldName the field name
     * @return mixed the formatted input field that passed validation
     * @throws ValidationException if validation fails
     */
    function validate(IRequest $Request, $fieldName);

}

