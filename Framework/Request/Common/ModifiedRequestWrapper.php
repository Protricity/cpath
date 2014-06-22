<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 3/24/14
 * Time: 4:06 PM
 */
namespace CPath\Framework\Request\Common;

use CPath\Base;
use CPath\Framework\Request\Interfaces\IRequest;

class ModifiedRequestWrapper extends RequestWrapper
{
    private $mPath=null, $mArgs, $mMethod;

    public function __construct(IRequest $Request, $newArgs=null, $newPrefix=null) {
        parent::__construct($Request);

        if($newPrefix) {
            list($newMethod, $newPath) = explode(' ', $newPrefix, 2);
            $this->mPath = rtrim($newPath, '/') . '/';
            $this->mMethod = $newMethod;
        }
        $this->mArgs = $newArgs;
    }

    function getArgs() {
        return $this->mArgs !== null ? $this->mArgs : parent::getArgs();
    }

    function getMatchedMethod() {
        return $this->mMethod;
    }

    function getMatchedPath() {
        return $this->mPath ?: $this->getOriginalRequest()->getPath();
    }

    // Static

    static function fromRequest($newPath=null, $newArgs=null) {
        return new ModifiedRequestWrapper(Base::getRequest(), $newArgs, $newPath);
    }
}

