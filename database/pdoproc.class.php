<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Database;
abstract class PDOProc extends PDOTable {
    public function call() {
        call_user_func_array($this, func_get_args());
    }
}