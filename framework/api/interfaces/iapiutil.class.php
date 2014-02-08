<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Interfaces;

use CPath\Interfaces\ILogEntry;

interface IAPIUtil extends IAPI{
    /**
     * Enable or disable logging for this IAPI
     * @param bool $enable set true to enable and false to disable
     * @return $this Return the class instance
     */
    function enableLog($enable=true);

    /**
     * Get captured logs
     * @return ILogEntry[]
     */
    function getLogs();

    /**
     * Get an API field by name
     * @param String $fieldName the field name
     * @return IField
     * @throws FieldNotFound if the field was not found
     */
    public function getField($fieldName);
}