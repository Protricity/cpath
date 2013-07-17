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
use CPath\Interfaces\IStaticHandler;
use CPath\Interfaces\IHandler;
use CPath\Model\DB\PDODatabase;

class UserNotFoundException extends \Exception {}
class UserAlreadyExistsException extends \Exception {}
class IncorrectUsernameOrPasswordException extends \Exception {}
class PasswordsDoNotMatchException extends \Exception {}

interface IUser {
    /**
     * @return PDODatabase
     */
    static function getDB();
}

abstract class User extends ArrayObject implements IUser, IStaticHandler, IResponseAggregate {
    Const TableName = 'users';
    Const ID = 'id';
    Const NAME = 'name';
    Const EMAIL = 'email';
    Const PASSWORD = 'password';
    Const FLAGS = 'flags';
    Const SETTINGS = 'settings';

    private $mID;
    private $mData = NULL;

    public function __construct($id) {
        if(is_numeric($id)) {
            $this->mID = $id;
        } else {
            $DB = $this->getDB();
            $SQL = "SELECT * FROM ".static::TableName
                ."\n WHERE ".static::NAME." = ".$DB->quote($id)
                ."\n OR ".static::EMAIL." = ".$DB->quote($id);
            $this->mData = $DB->query($SQL)->fetch();
            if(!$this->mData)
                throw new UserNotFoundException("User '$id' not found");
            $this->mID = intval($this->mData[static::ID]);
        }
    }


    public function getID() { return $this->mID; }
    public function getName() { return $this->getField(static::NAME); }
    public function getEmail() { return $this->getField(static::EMAIL); }

    public function checkPassword($password) {
        $hash = $this->getField(static::PASSWORD);
        if(static::hash($password, $hash) != $hash)
            throw new IncorrectUsernameOrPasswordException("The username/email and or password was not found");
    }

    public function setPassword($newPassword) {
        $this->setData(static::PASSWORD, static::hash($newPassword));
    }

    public function &getData() {
        if($this->mData)
            return $this->mData;
        $SQL = "SELECT * FROM ".static::TableName
            ."\n WHERE ".static::ID." = ".$this->mID;

        $this->mData = static::getDB()->query($SQL)->fetch();
        if(!$this->mData)
            throw new UserNotFoundException("User ID #{$this->mID} not found");
        return $this->mData;
    }

    protected function getField($key=null) {
        $data = $this->getData();
        return $data[$key];
    }

    public function setData($field, $value) {
        $DB = static::getDB();
        $SQL = "UPDATE ".static::TableName
            ."\n SET {$field} = ".$DB->quote($value)
            ."\n WHERE ".static::ID." = ".$this->mID;
        $DB->exec($SQL);
        $this->mData[$field] = $value;
    }

    /**
     * @return IResponse
     */
    public function getResponse()
    {
        return new Response("Retrieved User '" . $this->getName() . "'", true, $this->getData());
    }

    // Statics

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