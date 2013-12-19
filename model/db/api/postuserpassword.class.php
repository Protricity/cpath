<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Base;
use CPath\Handlers\Api\Interfaces\APIException;
use CPath\Handlers\Api\PasswordField;
use CPath\Handlers\API;
use CPath\Handlers\Api\Validation;
use CPath\Describable\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Model\Response;

class API_PostUserPassword extends API_Base {

    const FIELD_PASSWORD = 'new_password';
    const FIELD_OLD_PASSWORD = 'old_password';
    const FIELD_CONFIRM_PASSWORD = 'confirm_password';

    private $mConfirm = false, $mLoggedIn = false, $mUser;

    /**
     * Construct an instance of this API
     * @param PDOUserModel $User the user source object for this API
     */
    function __construct(PDOUserModel $User) {
        if(!Base::isCLI() && $SessionUser = $User::loadBySession(false, false)) {
            $User = $SessionUser;
            $this->mLoggedIn = true;
            $this->mConfirm = !$SessionUser->isAdmin();
        }
        $this->mUser = $User;
        parent::__construct($this->mUser);
    }

    protected function setupFields() {
        if(!$this->mLoggedIn)
            throw new APIException("User must be logged in to change password");

        /** @var PDOUserModel $User  */
        $User = $this->mUser;
        $this->addField(self::FIELD_PASSWORD, new PasswordField("Password"));
        $THIS = $this;
        if($User::PASSWORD_CONFIRM) {
            $this->addField(self::FIELD_CONFIRM_PASSWORD, new PasswordField("Confirm Password"));
            $this->addValidation(new Validation(function(IRequest $Request) use ($User, $THIS) {
                $pass = $Request[$THIS::FIELD_PASSWORD];
                $confirm = $Request->pluck($THIS::FIELD_CONFIRM_PASSWORD);
                $User::confirmPassword($pass, $confirm);
            }));
        }

        if($this->mConfirm) {
            $confirm = $this->mConfirm;
            $this->addField(self::FIELD_OLD_PASSWORD, new PasswordField("Password"));
            $this->addValidation(new Validation(function(IRequest $Request) use ($User, $THIS, $confirm) {
                if($confirm) {
                    $old = $Request->pluck($THIS::FIELD_OLD_PASSWORD);
                    try {
                        $User->checkPassword($old);
                    } catch (IncorrectUsernameOrPasswordException $ex) {
                        throw new IncorrectUsernameOrPasswordException("Old password was not correct");
                    }
                }
            }));
        }

        $this->generateFieldShorts();
    }

    /**
     * Get the Object Description
     * @return \CPath\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        if($this->mLoggedIn)
            return "Change Account Password for " . $this->mUser;
        return "Change Account Password (Requires user session)";
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final protected function doExecute(IRequest $Request) {
        $User = $this->mUser;
        $pass = $Request[self::FIELD_PASSWORD];
        $User->changePassword($pass);
        return new Response("User password changed successfully", false, $User);
    }
}
