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
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\Response;

class API_PostUser extends API_Post {
    private $mUser;

    /**
     * Construct an instance of this API
     * @param PDOUserModel $Model the user source object for this API
     */
    function __construct(PDOUserModel $Model) {
        $this->mUser = $Model;

        $this->addField($Model::ColumnUsername, new APIRequiredField("Username"));
        $this->addField($Model::ColumnEmail, new APIRequiredField("Email Address", FILTER_VALIDATE_EMAIL));

        parent::__construct($Model);

        if($Model::ConfirmPassword)
            $this->addField($Model::ColumnPassword.'_confirm', new APIRequiredField("Confirm Password"));
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
        $this->processRequest($Request);
        $User::validateRequest($Request);
        $login = $Request->pluck('login');
        $pass = $Request[$User::ColumnPassword];

        if($User::ConfirmPassword) {
            $confirm = $Request->pluck($User::ColumnPassword.'_confirm');
            $User::confirmPassword($pass, $confirm);
        }
        $Request[$User::ColumnPassword] = $User::hashPassword($pass);
        /** @var PDOUserModel $User */

        $this->beforeInsert($Request);

        $User = $User::createFromArray($Request);

        $this->beforeInsert($User, $Request);

        if($login) {
            $User::login($User->getUsername(), $pass);
            return new Response("Created and logged in user '".$User->getUsername()."' successfully", true, $User);
        }
        return new Response("Created user '".$User->getUsername()."' successfully", true, $User);
    }
}
