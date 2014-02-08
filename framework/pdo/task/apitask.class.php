<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;

use CPath\Framework\Api\Interfaces\IAPI;
use CPath\Framework\Task\AbstractTask;
use CPath\Framework\Task\Exceptions\InvalidTaskStateException;
use CPath\Framework\Request\Interfaces\IRequest;

class APITaskException extends \Exception {}

abstract class APITask extends AbstractTask {

    private $mAPI;
    function __construct(IAPI $API) {
        $this->mAPI = $API;
    }

    /**
     * @return \CPath\Framework\Request\IRequest
     */
    abstract function getRequest();

    /**
     * Start the task.
     * @param int $eventFlags existing task flags
     * @return int return new (or modified) flags
     * @throws InvalidTaskStateException
     */
    protected function start($eventFlags)
    {
        $Request = $this->getRequest();
        $this->mAPI->execute($Request);
    }
}