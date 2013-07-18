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

    const FLAG_DEBUG = 0x01;
    const FLAG_VALIDATED = 0x02;
    const FLAG_DISABLED = 0x04;

    const FLAG_ADMIN = 0x10;

    private $mData = NULL;
    private $mCommit = array();

    public function __construct($id) {
        $DB = $this->getDB();
        if(is_numeric($id)) {
            $SQL = "SELECT * FROM ".static::TableName
                ."\n WHERE ".static::ID." = ".intval($id);
        } else {
            $SQL = "SELECT * FROM ".static::TableName
                ."\n WHERE ".static::NAME." = ".$DB->quote($id)
                ."\n OR ".static::EMAIL." = ".$DB->quote($id);
        }
        $this->mData = $DB->query($SQL)->fetch();
        if(!$this->mData)
            throw new UserNotFoundException("User '$id' not found");
        if(static::FLAGS)
            $this->mData[static::FLAGS] = (int)$this->mData[static::FLAGS];
    }


    public function getID() { return $this->mData[static::ID]; }
    public function getName() { return $this->mData[static::NAME]; }
    public function getEmail() { return $this->mData[static::EMAIL]; }
    public function isAdmin() { return $this->isFlag(static::FLAG_ADMIN); }
    public function isDebug() { return $this->isFlag(static::FLAG_DEBUG); }

    public function isFlag($flags) {
        if(!static::FLAGS)
            throw new \Exception("Flags are not enableld for this user type: ".get_class($this));
        return $this->mData[static::FLAGS] & $flags ? true : false;
    }

    public function checkPassword($password) {
        $hash = $this->mData[static::PASSWORD];
        if(static::hash($password, $hash) != $hash)
            throw new IncorrectUsernameOrPasswordException("The username/email and or password was not found");
    }

    public function setFlags($flags, $commit=true, $remove=false) {
        if(!$remove)
            $flags |= $this->mData[static::FLAGS];
        else
            $flags = $this->mData[static::FLAGS] & ~$flags;
        $this->setData(static::FLAGS, $flags, $commit);
    }

    public function setPassword($newPassword, $commit=true) {
        $this->setData(static::PASSWORD, static::hash($newPassword), $commit);
    }

    public function &getData() { return $this->mData; }

    public function setData($field, $value, $commit=true) {
        $this->mCommit[$field] = $value;
        if($commit) {
            $set = '';
            $DB = static::getDB();
            foreach($this->mCommit as $field=>$value)
                $set .= ($set ? ",\n\t" : '') . "{$field} = ".$DB->quote($value);
            $SQL = "UPDATE ".static::TableName
                ."\n SET {$set}"
                ."\n WHERE ".static::ID." = ".$this->getID();
            $DB->exec($SQL);
            $this->mCommit = array();
        }
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