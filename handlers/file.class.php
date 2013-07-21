<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;

use CPath\Interfaces\IHandler;
use CPath\Interfaces\IRoute;

class File implements IHandler{
    const Build_Ignore = true;

    const Route_Methods = 'GET';
    const Route_Path = NULL;

    private $mFilePath;

    public function __construct($filePath) {
        $this->mFilePath = $filePath;
    }

    public function render(IRoute $Route)
    {
        include($this->mFilePath);
        return true;
    }
}