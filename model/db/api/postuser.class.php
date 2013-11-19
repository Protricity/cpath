<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\Api\Field;
use CPath\Handlers\Api\Interfaces\IField;
use CPath\Handlers\Api\Interfaces\InvalidAPIException;
use CPath\Handlers\Api\PasswordField;
use CPath\Handlers\Api\Validation;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\DB\Interfaces\IAPIPostCallbacks;
use CPath\Model\DB\Interfaces\IAPIPostUserCallbacks;
use CPath\Model\Response;


class API_PostUser extends API_Post implements IAPIPostCallbacks {
    /** @var PDOUserModel */
    private $mUser;

    const FIELD_LOGIN = 'login';

    /**
     * Add or modify fields of an API.
     * Note: Leave empty if unused.
     * @param Array &$fields the existing API fields to modify
     * @return IField[]|NULL return an array of prepared fields to use or NULL to ignore.
     * @throws InvalidAPIException
     */
    function preparePostFields(Array &$fields){
        /** @var PDOUserModel $Model  */
        $Model = $this->getModel();
        $this->mUser = $Model;

        if($Model::PASSWORD_CONFIRM) {
            if(!$Model::COLUMN_PASSWORD)
                throw new InvalidAPIException("::PASSWORD_CONFIRM requires ::COLUMN_PASSWORD set");
            if(!isset($fields[$Model::COLUMN_PASSWORD]))
                throw new InvalidAPIException("Column '" . $Model::COLUMN_PASSWORD . "' does not exist in field list");
            $fields[$Model::COLUMN_PASSWORD.'_confirm'] = new PasswordField("Confirm Password");
            $this->addValidation(new Validation(function(IRequest $Request) use ($Model) {
                $confirm = $Request->pluck($Model::COLUMN_PASSWORD.'_confirm');
                $pass = $Request[$Model::COLUMN_PASSWORD];
                $Model::confirmPassword($pass, $confirm);
            }));
        }
        $fields[self::FIELD_LOGIN] = new Field("Log in after");
        $this->generateFieldShorts();
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
    function preparePostInsert(Array &$row, IRequest $Request) {

        $User = $this->mUser;
        $name = $Request[$User::COLUMN_USERNAME];
        $pass = $Request[$User::COLUMN_PASSWORD];
        $login = $Request[self::FIELD_LOGIN] ? true : false;
        if($User::searchByColumns($name, $User::COLUMN_USERNAME)->fetch()) {
            if($login) {
                try {
                    $User::login($name, $pass);
                } catch (IncorrectUsernameOrPasswordException $ex) {
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
     * @param PDOModel|PDOUserModel $NewModel the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     * @throws ModelAlreadyExistsException if the user already exists
     */
    function onPostExecute(PDOModel $NewModel, IRequest $Request, IResponse $Response)
    {
        $User = $NewModel;
        $pass = $Request[$User::COLUMN_PASSWORD];
        $login = $Request[self::FIELD_LOGIN] ? true : false;


        if($login && $pass) {
            $User::login($User->getUsername(), $pass);
            $Response = new Response("Created and logged in user '".$User->getUsername()."' successfully", true, $User);
        } else {
            $Response = new Response("Created user '".$User->getUsername()."' successfully", true, $User);
        }

        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof IAPIPostUserCallbacks)
                $Response = $Handler->onPostUserExecute($User, $Request, $Response) ?: $Response;

        return $Response;
    }
}
