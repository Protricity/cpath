<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;
interface ILogEntry { // extends IXML, IJSON
    const LEVEL = 0;
    function getMessage();
    function getTag();
}