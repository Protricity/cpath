<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;


use CPath\Base;
use CPath\Framework\API\Exceptions\APIException;
use CPath\Framework\API\Field\PasswordField;
use CPath\Framework\API\Validation\CallbackValidation;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Request\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;
use CPath\Framework\Response\Types\DataResponse;
use CPath\Framework\User\Predicates\IsAdmin;
use CPath\Framework\User\Util\UserUtil;

class PostUserPasswordAPI extends AbstractPDOAPI {

    const FIELD_PASSWORD = 'new_password';
    const FIELD_OLD_PASSWORD = 'old_password';
    const FIELD_CONFIRM_PASSWORD = 'confirm_password';

    private $mConfirm = false, $mLoggedIn = false, $mTable, $mUser = null;

    /**
     * Construct an instance of this API
     * @param \CPath\Framework\PDO\Templates\User\Table\PDOUserTable $Table the user source object for this API
     */
    function __construct(PDOUserTable $Table) {
        if(!Base::isCLI() && $SessionUser = $Table->loadBySession(false, false)) {
            $Util = new UserUtil($SessionUser);
            $this->mUser = $SessionUser;
            $this->mLoggedIn = true;
            $this->mConfirm = !$Util->hasRole(new IsAdmin);
        }
        $this->mTable = $Table;
        parent::__construct($this->mTable);
    }

    protected function setupFields() {
        $T = $this->mTable;
        if(!$this->mLoggedIn)
            throw new APIException("User must be logged in to change password");

        /** @var PDOUserModel $User  */
        $this->addField(self::FIELD_PASSWORD, new PasswordField("Password"));
        $THIS = $this;
        if($T::PASSWORD_CONFIRM) {
            $this->addField(self::FIELD_CONFIRM_PASSWORD, new PasswordField("Confirm Password"));
            $this->addValidation(new CallbackValidation(function(IRequest $Request) use ($T, $THIS) {
                $pass = $Request[$THIS::FIELD_PASSWORD];
                $confirm = $Request->pluck($THIS::FIELD_CONFIRM_PASSWORD);
                $T->confirmPassword($pass, $confirm);
            }));
        }

        if($this->mConfirm) {
            $confirm = $this->mConfirm;
            $this->addField(self::FIELD_OLD_PASSWORD, new PasswordField("Password"));
            $this->addValidation(new CallbackValidation(function(IRequest $Request) use ($User, $THIS, $confirm) {
                if($confirm) {
                    $old = $Request->pluck($THIS::FIELD_OLD_PASSWORD);
                    //try {
                        $User->checkPassword($old);
                    //} catch (IncorrectUsernameOrPasswordException $ex) {
                    //    throw new IncorrectUsernameOrPasswordException("Old password was not correct");
                    //}
                }
            }));
        }

        //$this->generateFieldShorts();
    }

    /**
     * Get the Object Description
     * @return \CPath\Describable\IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        if($this->mLoggedIn)
            return "Change Account Password for " . $this->mTable;
        return "Change Account Password (Requires user session)";
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param \CPath\Request\IRequest $Request the IRequest instance for this render which contains the request and args
     * @param Array $args additional arguments for this execution
     * @return IResponse the api call response with data, message, and status
     */
    final function execute(IRequest $Request, $args) {
        $T = $this->mTable;
        $pass = $Request[self::FIELD_PASSWORD];
        $SessionUser = $T->loadBySession(true, false);
        $SessionUser->changePassword($pass);
        return new DataResponse("User password changed successfully", false, $SessionUser);
    }
}
