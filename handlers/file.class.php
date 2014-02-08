<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Handlers;

use CPath\Interfaces\IHandler;
use CPath\Framework\Request\Interfaces\IRequest;

class File implements IHandler{
    const BUILD_IGNORE = true;

    private $mFilePath;

    public function __construct($filePath) {
        $this->mFilePath = $filePath;
    }

    public function render(IRequest $Request)
    {
        include($this->mFilePath);
        return true;
    }
}