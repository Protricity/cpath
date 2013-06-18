<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Database;
use CPath\Interfaces\IDatabase;

class NotConfiguredException extends \Exception {}
abstract class DataBase implements IDatabase {
    private $mPrefix;
    protected function __construct($prefix) {
        $this->mPrefix = $prefix;
    }

    public function getPrefix() { return $this->mPrefix; }

    static function get()
    {
        throw new NotConfiguredException("Database helper ".get_called_class()."::get() is missing");
    }
}