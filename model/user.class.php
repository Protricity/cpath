<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 7/9/13
 * Time: 3:37 PM
 */
namespace CPath\Model;

use CPath\Model\DB\PDODatabase;

class UserNotFoundException extends \Exception {}
class IncorrectUsernameOrPassword extends \Exception {}

abstract class User extends ArrayObject {
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
        if(is_int($id)) {
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

    /**
     * @return \PDO
     */
    abstract function getDB();

    public function getID() { return $this->mID; }
    public function getName() { return $this->getData(static::NAME); }
    public function getEmail() { return $this->getData(static::EMAIL); }

    public function checkPassword($password) {
        $hash = $this->getData(static::PASSWORD);
        if(crypt($password, $hash) != $hash)
            throw new IncorrectUsernameOrPassword("The username/email and or password was not found");
    }

    public function setPassword($newPassword) {
        $this->setData(static::PASSWORD, crypt($newPassword));
    }

    public function &getData($key=null) {
        if($this->mData)
            return $key ? $this->mData[$key] : $this->mData;
        $SQL = "SELECT * FROM ".static::TableName
            ."\n WHERE ".static::ID." = ".$this->mID;
        $this->mData = $this->getDB()->query($SQL)->fetch();
        if(!$this->mData)
            throw new UserNotFoundException("User '{$this->mID}' not found");
        return $key ? $this->mData[$key] : $this->mData;
    }

    public function setData($field, $value) {
        $DB = $this->getDB();
        $SQL = "UPDATE ".static::TableName
            ."\n SET {$field} = ".$DB->quote($value)
            ."\n WHERE ".static::ID." = ".$this->mID;
        $DB->exec($SQL);
        $this->mData[$field] = $value;
    }
}