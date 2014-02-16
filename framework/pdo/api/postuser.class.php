<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;


use CPath\Framework\Api\Exceptions\APIException;
use CPath\Framework\Api\Field\Field;
use CPath\Framework\Api\Field\PasswordField;
use CPath\Framework\Api\Interfaces\IField;
use CPath\Framework\Api\Validation\CallbackValidation;
use CPath\Framework\PDO\Interfaces\IAPIPostCallbacks;
use CPath\Framework\PDO\Interfaces\IAPIPostUserCallbacks;
use CPath\Framework\PDO\Model\PDOModel;
use CPath\Framework\PDO\Table\ModelAlreadyExistsException;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Framework\User\Role\Exceptions\AuthenticationException;


class API_PostUser extends API_Post implements IAPIPostCallbacks {

    const FIELD_LOGIN = 'login';

    /**
     * Construct an instance of the GET API
     * @param \CPath\Framework\PDO\Templates\User\Table\PDOUserTable $Table the PDOTable for this API
     * PRIMARY key is already included
     */
    function __construct(PDOUserTable $Table) {
        parent::__construct($Table);
    }

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     * @throws APIException
     */
    // TODO: broken?
    final function preparePostFields(Array &$fields){
        /** @var PDOUserTable $T  */
        $T = $this->getTable();

        if($T::PASSWORD_CONFIRM) {
            if(!$T::COLUMN_PASSWORD)
                throw new APIException("::PASSWORD_CONFIRM requires ::COLUMN_PASSWORD set"); // TODO: move to builder
            if(!isset($fields[$T::COLUMN_PASSWORD]))
                throw new APIException("Column '" . $T::COLUMN_PASSWORD . "' does not exist in field list");
            $fields[$T::COLUMN_PASSWORD.'_confirm'] = new PasswordField("Confirm Password");
            $this->addValidation(new CallbackValidation(function(IRequest $Request) use ($T) {
                $confirm = $Request->pluck($T::COLUMN_PASSWORD.'_confirm');
                $pass = $Request[$T::COLUMN_PASSWORD];
                $T->confirmPassword($pass, $confirm);
            }));
        }
        $fields[self::FIELD_LOGIN] = new Field(self::FIELD_LOGIN, "Log in after");

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostUserCallbacks)
                $fields = $Handler->preparePostUserFields($fields) ?: $fields;

        //$this->generateFieldShorts();
    }

    /**
     * Modify the API_Post IRequest and/or return a row of fields to use in PDOModel::createFromArray
     * Note: Leave empty if unused.
     * @param Array &$row an associative array of key/value pairs
     * @param IRequest $Request
     * @return Array|null a row of key/value pairs to insert into the database
     * @throws ModelAlreadyExistsException if the account already exists
     * Note: a log in may occur if field 'login' == true and the password is correct
     */
    final function preparePostInsert(Array &$row, IRequest $Request) {

        /** @var PDOUserTable $T  */
        $T = $this->getTable();

        $name = $Request[$T::COLUMN_USERNAME];
        $pass = $Request[$T::COLUMN_PASSWORD];
        $login = $Request[self::FIELD_LOGIN] ? true : false;
        if($T->searchByColumns($name, $T::COLUMN_USERNAME)->fetch()) {
            if($login) {
                try {
                    $T->login($name, $pass);
                } catch (AuthenticationException $ex) {
                    throw new ModelAlreadyExistsException("This user already exists, and the login failed");
                }
                throw new ModelAlreadyExistsException("This user already exists, but you were successfully logged in");
            }
            throw new ModelAlreadyExistsException("This user already exists");
        }

        unset($row[self::FIELD_LOGIN]);
    }

    /**
     * Perform on successful API_Get execution
     * @param PDOModel|PDOUserModel $NewUser the new user account instance
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     * @throws ModelAlreadyExistsException if the user already exists
     */
    final function onPostExecute(PDOModel $NewUser, IRequest $Request, IResponse $Response) {
        /** @var \CPath\Framework\PDO\Templates\User\Table\PDOUserTable $T  */
        $T = $this->getTable();

        $pass = $Request[$T::COLUMN_PASSWORD];
        $login = $Request[self::FIELD_LOGIN] ? true : false;


        if($login && $pass) {
            $T->login($NewUser->getUsername(), $pass);
            $Response = new DataResponse("Created and logged in user '".$NewUser->getUsername()."' successfully", true, $NewUser);
        } else {
            $Response = new DataResponse("Created user '".$NewUser->getUsername()."' successfully", true, $NewUser);
        }

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostUserCallbacks)
                $Response = $Handler->onPostUserExecute($NewUser, $Request, $Response) ?: $Response;

        return $Response;
    }
}
