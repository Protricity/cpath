<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\Api\RequiredField;
use CPath\Interfaces\IDescribable;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;

class API_PostUserRecover extends API_Base {

    const FIELD_NAME = 'name';

    private $mUser;

    /**
     * Construct an instance of this API
     * @param PDOUserModel $User the user source object for this API
     */
    function __construct(PDOUserModel $User) {
        $this->mUser = $User;
        parent::__construct($this->mUser);
    }

    protected function setupFields() {
        /** @var PDOUserModel $User  */
        //$User = $this->mUser;
        $this->addField(self::FIELD_NAME, new RequiredField("User name or email"));
        $this->generateFieldShorts();
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Recover " . $this->mUser;
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final protected function doExecute(IRequest $Request) {
        //return new Response("User password changed successfully", false, $User);
    }
}
