<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\Api\Field;
use CPath\Framework\Api\Interfaces\IField;

/**
 * Class APIRquiredParam
 * @package CPath
 * Represents a Required Parameter from the route path
 */
class RequiredParam extends Field {
    protected function getDefaultFlags() { return parent::getDefaultFlags() | IField::IS_REQUIRED | IField::IS_PARAMETER; }
}
