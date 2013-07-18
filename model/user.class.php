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
use CPath\Model\DB\PDODatabase;
use CPath\Model\DB\PDOModel;

class UserNotFoundException extends \Exception {}
class UserAlreadyExistsException extends \Exception {}
class IncorrectUsernameOrPasswordException extends \Exception {}
class PasswordsDoNotMatchException extends \Exception {}


abstract class User extends PDOModel implements IHandlerAggregate {

    const TableName = 'user';
    const ID = 'id';
    const NAME = 'name';
    const EMAIL = 'email';
    const PASSWORD = 'password';
    const FLAGS = 'flags';
    const CONFIG = 'config';

    const FLAG_VALIDATED = 0x02;
    const FLAG_DISABLED = 0x04;

    const FLAG_DEBUG = 0x10;
    const FLAG_MANAGER = 0x20;
    const FLAG_ADMIN = 0x40;

    private $mCommit = array();

    public function __construct($id) {
        $DB = static::getDB();
        if(is_numeric($id)) {
            $row = $DB->select(static::TableName, '*')
                ->where(static::ID, $id)
                ->fetch();
        } else {
            $row = $DB->select(static::TableName, '*')
                ->where(static::NAME, $id)
                ->where('OR')
                ->where(static::EMAIL, $id)
                ->fetch();
        }
        if(!$row)
            throw new UserNotFoundException("User '$id' not found");
        if(static::FLAGS)
            $row[static::FLAGS] = (int)$row[static::FLAGS];
        parent::__construct($row);
    }

    public function getID() { return $this->mRow[static::ID]; }
    public function getName() { return $this->mRow[static::NAME]; }
    public function getEmail() { return $this->mRow[static::EMAIL]; }
    public function isAdmin() { return $this->isFlag(static::FLAG_ADMIN); }
    public function isDebug() { return $this->isFlag(static::FLAG_DEBUG); }

    public function isFlag($flags) {
        if(!static::FLAGS)
            throw new \Exception("Flags are not enableld for this user type: ".get_class($this));
        return $this->mRow[static::FLAGS] & $flags ? true : false;
    }

    public function checkPassword($password) {
        $hash = $this->mRow[static::PASSWORD];
        if(static::hash($password, $hash) != $hash)
            throw new IncorrectUsernameOrPasswordException("The username/email and or password was not found");
    }

    public function setFlags($flags, $commit=true, $remove=false) {
        if(!$remove)
            $flags |= $this->mRow[static::FLAGS];
        else
            $flags = $this->mRow[static::FLAGS] & ~$flags;
        $this->setField(static::FLAGS, $flags, $commit);
    }

    public function setPassword($newPassword, $commit=true) {
        $this->setField(static::PASSWORD, static::hash($newPassword), $commit);
    }

    // Statics

    protected static function getPrimaryKeyField() {
        return static::ID;
    }

    protected static function hash($password, $oldPassword=NULL) {
        return crypt($password, $oldPassword);
    }

    /**
     * @param String $name
     * @param String $email
     * @param String $password
     * @param String|null $passwordConfirm
     * @return User
     * @throws PasswordsDoNotMatchException
     * @throws UserAlreadyExistsException
     * @throws \Exception|\PDOException
     */
    public static function create($name, $email, $password, $passwordConfirm=null) {
        if($passwordConfirm !== null && $password != $passwordConfirm)
            throw new PasswordsDoNotMatchException("Please confirm passwords match");

        $password = static::hash($password);

        $DB = static::getDB();

        try {
            $id = $DB->insert(static::TableName, static::NAME, static::EMAIL, static::PASSWORD)
                ->requestInsertID(static::ID)
                ->values($name, $email, $password)
                ->getInsertID();
        } catch (\PDOException $ex) {
            if(strpos($ex->getMessage(), 'Duplicate')!==false)
                throw new UserAlreadyExistsException($ex->getMessage(), $ex->getCode(), $ex);
            throw $ex;
        }

        return new static(intval($id));
    }

    public static function delete(User $User) {
        $DB = static::getDB();
        $c = $DB->delete(static::TableName)
            ->where(static::ID, $User->getID())
            ->execute()
            ->getDeletedRows();
        if(!$c)
            throw new \Exception("Unable to delete User #"+$User->getID());
    }

    /**
     * @return IHandler $Handler
     */
    static function getHandler()
    {
        $Class = get_called_class();

        $Handlers = new HandlerSet();
        $Handlers->addHandler('get', new SimpleApi(function(Api $API, Array $request) use ($Class) {
            $request = $API->processRequest($request);
            return new $Class(is_numeric($request['search']) ? intval($request['search']) : $request['search']);
        }, array(
            'search' => new ApiParam(),
        )));


        $Handlers->addHandler('new', new SimpleApi(function(Api $API, Array $request) use ($Class) {
            $request = $API->processRequest($request);
            return $Class::create($request['name'], $request['email'], $request['password'], $request['confirmPassword']);
        }, array(
            'name' => new ApiParam(),
            'email' => new ApiRequiredFilterField(FILTER_VALIDATE_EMAIL, 0, "Email Address"),
            'password' => new ApiRequiredField(),
            'confirmPassword' => new ApiField(),
        )));

        return $Handlers;
    }
}