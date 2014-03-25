<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\API\Field;

use CPath\Framework\API\Field\Interfaces\IField;

class EnumParam extends EnumField  {
    protected function getDefaultFlags() { return parent::getDefaultFlags() | IField::IS_PARAMETER; }
}
