<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;

use CPath\Interfaces\IHandler;

class File implements IHandler{
    const BUILD_IGNORE = true;

    const ROUTE_METHODS = 'GET';
    const ROUTE_PATH = NULL;

    private $mFilePath;

    public function __construct($filePath) {
        $this->mFilePath = $filePath;
    }

    public function render(Array $args)
    {
        include($this->mFilePath);
        return true;
    }
}