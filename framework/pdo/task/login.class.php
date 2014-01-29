<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;


use CPath\Framework\PDO\Templates\User\PDOUserModel;
use CPath\Framework\Task\ITask;
use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\API;
use CPath\Handlers\Api\Tasks\APITask;
use CPath\Handlers\Themes\Interfaces\ITheme;

class Task_Login extends APITask {
    private $mUser;
    function __construct(PDOUserModel $User, ITheme $Theme=null) {
        parent::__construct($Theme);
        $this->mUser = $User;
    }

    /**
     * @return IAPI
     */
    protected function loadAPI() {
        return new API_PostUserLogin($this->mUser);
    }

    function getUser() { return $this->mUser; }

    /**
     * Return the task status flags.
     * Note: if the flag for TASK_ACTIVE is not set, the task is generally seen as inactive or unavailable.
     * @return int
     */
    function processTaskState()
    {
        $flags = 0;
        $Model = $this->mUser;
        if($User = $Model::loadBySession(false, false))
            ;
        else
            $flags |= ITask::STATUS_ACTIVE;
        return $flags;
    }
}
