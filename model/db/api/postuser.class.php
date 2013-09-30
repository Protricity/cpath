<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\APIField;
use CPath\Handlers\APIRequiredField;
use CPath\Handlers\APIValidation;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\InvalidAPIException;
use CPath\Model\Response;

interface IPostUserExecute {

    /**
     * Perform on successful API_Get execution
     * @param PDOUserModel $NewUser the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     */
    function onPostUserExecute(PDOUserModel $NewUser, IRequest $Request, IResponse $Response);
}

class API_PostUser extends API_Post implements IPostExecute {
    private $mUser;

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     * @throws InvalidAPIException if API was not set up properly
     */
    protected function setupAPI() {
        /** @var PDOUserModel $Model  */
        $Model = $this->getModel();
        $this->mUser = $Model;

        parent::__construct($Model);

        if($Model::PASSWORD_CONFIRM) {
            if(!$Model::COLUMN_PASSWORD)
                throw new InvalidAPIException("::PASSWORD_CONFIRM requires ::COLUMN_PASSWORD set");
            $this->getField($Model::COLUMN_PASSWORD); // Ensure the password field is in the insert
            $this->addField($Model::COLUMN_PASSWORD.'_confirm', new APIRequiredField("Confirm Password"));
            $this->addValidation(new APIValidation(function(IRequest $Request) use ($Model) {
                $confirm = $Request->pluck($Model::COLUMN_PASSWORD.'_confirm');
                $pass = $Request[$Model::COLUMN_PASSWORD];
                $Model::confirmPassword($pass, $confirm);
            }));
        }
        $this->addField('login', new APIField("Log in after"));
    }

    /**
     * Perform on successful API_Get execution
     * @param PDOModel $NewModel the returned model
     * @param IRequest $Request
     * @param IResponse $Response
     * @return IResponse|null
     * @throws ModelAlreadyExistsException if the user already exists
     */
    function onPostExecute(PDOModel $NewModel, IRequest $Request, IResponse $Response)
    {
        $User = $this->mUser;
        $pass = $Request[$User::COLUMN_PASSWORD];
        $name = $Request[$User::COLUMN_USERNAME];
        $this->processRequest($Request);
        $login = $Request->pluck('login');

        if($User::searchByColumns($name, $User::COLUMN_USERNAME)->fetch())
            throw new ModelAlreadyExistsException("This user already exists");

        $User = parent::execute($Request)->getDataPath();

        if($login && $pass) {
            $User::login($User->getUsername(), $pass);
            return new Response("Created and logged in user '".$User->getUsername()."' successfully", true, $User);
        }
        $Response = new Response("Created user '".$User->getUsername()."' successfully", true, $User);

        if($this instanceof IPostUserExecute)
            $Response = $this->onPostUserExecute($User, $Request, $Response) ?: $Response;

        return $Response;
    }
}
