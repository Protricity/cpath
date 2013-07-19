<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/9/13
 * Time: 3:37 PM
 */
namespace CPath\Model;

use CPath\Handlers\Api;
use CPath\Handlers\ApiField;
use CPath\Handlers\ApiParam;
use CPath\Handlers\ApiRequiredField;
use CPath\Handlers\ApiRequiredFilterField;
use CPath\Handlers\HandlerSet;
use CPath\Handlers\SimpleApi;
use CPath\Interfaces\IResponseAggregate;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\IHandlerAggregate;
use CPath\Interfaces\IHandler;
use CPath\Interfaces\IUserSession;
use CPath\Model\DB\ModelAlreadyExistsException;
use CPath\Model\DB\ModelNotFoundException;
use CPath\Model\DB\PDODatabase;
use CPath\Model\DB\PDOModel;

class UserNotFoundException extends ModelNotFoundException {}
class UserAlreadyExistsException extends ModelAlreadyExistsException {}
class IncorrectUsernameOrPasswordException extends \Exception {}
class PasswordsDoNotMatchException extends \Exception {}

class SessionManager {
    const SESSION_KEY = '_session';
    const SESSION_KEY_LENGTH = 48;

    const FLAG_VALIDATED = 0x02;
    const FLAG_DISABLED = 0x04;

    const FLAG_DEBUG = 0x10;
    const FLAG_MANAGER = 0x20;
    const FLAG_ADMIN = 0x40;

    /** @var IUserSession */
    private $mUser;
    private $mFlags = NULL;
    private $mIsLoggedIn = false;

    public function __construct(IUserSession $User) {
        $this->mUser = $User;
        $this->mFlags = (int)$User->getFlags();
    }

    public function login($password) {
        $this->checkPassword($password);
        $key = openssl_random_pseudo_bytes(static::SESSION_KEY_LENGTH);
        $this->mUser->storeNewSessionKey($key, $this->mUser->getID());
        session_start();
        $_SESSION[self::SESSION_KEY] = $key;
        $this->mIsLoggedIn = true;
    }

    public function isLoggedIn() {
        return $this->mIsLoggedIn;
    }

    public function isFlag($flags) {
        return $this->mFlags & $flags ? true : false;
    }

    public function checkPassword($password) {
        $hash = $this->mUser->getPassword();
        if($this->hash($password, $hash) != $hash)
            throw new IncorrectUsernameOrPasswordException("The username/email and or password was not found");
    }

    public function setFlags($flags, $commit=true, $remove=false) {
        if(!$remove)
            $flags |= $this->mFlags;
        else
            $flags = $this->mFlags & ~$flags;
        $this->mFlags = $flags;
        $this->mUser->setFlags($flags, $commit);
    }

    public function changePassword($newPassword, $confirmPassword=NULL) {
        if($confirmPassword !== NULL)
            if($newPassword != $confirmPassword)
        $this->mUser->setPassword($this->hash($newPassword), true);
    }

    // Statics

    public static function checkForSessionKey() {
        if(!isset($_SESSION, $_SESSION[self::SESSION_KEY]))
            return false;
        return $_SESSION[self::SESSION_KEY];
    }

    protected function hash($password, $oldPassword=NULL) {
        return crypt($password, $oldPassword);
    }

    /**
     * @param HandlerSet $Handlers
     */
    public function addHandlers(HandlerSet $Handlers)
    {
        if(!$Handlers)
            $Handlers = new HandlerSet();
        $Handlers->addHandler('get', new SimpleApi(function(Api $API, Array $request) use ($Class) {
            $request = $API->processRequest($request);
            return new $Class(is_numeric($request['search']) ? intval($request['search']) : $request['search']);
        }, array(
            'search' => new ApiParam(),
        )));
    }
}