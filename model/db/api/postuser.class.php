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

        if($Model::ConfirmPassword) {
            if(!$Model::ColumnPassword)
                throw new InvalidAPIException("::ConfirmPassword requires ::ColumnPassword set");
            $this->getField($Model::ColumnPassword); // Ensure the password field is in the insert
            $this->addField($Model::ColumnPassword.'_confirm', new APIRequiredField("Confirm Password"));
            $this->addValidation(new APIValidation(function(IRequest $Request) use ($Model) {
                $confirm = $Request->pluck($Model::ColumnPassword.'_confirm');
                $pass = $Request[$Model::ColumnPassword];
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
        $login = $Request->pluck('login');

        parent::execute($Request);

        if($login && $pass = $Request[$User::ColumnPassword]) {
            $User::login($User->getUsername(), $pass);
            return new Response("Created and logged in user '".$User->getUsername()."' successfully", true, $User);
        }
        return new Response("Created user '".$User->getUsername()."' successfully", true, $User);
    }
}
