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

class API_PostUser extends API_Post {
    private $mUser;

    /**
     * Construct an instance of this API
     * @param PDOUserModel $Model the user source object for this API
     */
    function __construct(PDOUserModel $Model) {
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
     * Execute this API Endpoint with the entire request.
     * This method must call processRequest to validate and process the request object.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    function execute(IRequest $Request) {
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
        return new Response("Created user '".$User->getUsername()."' successfully", true, $User);
    }
}
