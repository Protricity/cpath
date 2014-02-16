<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;


use CPath\Describable\IDescribable;
use CPath\Framework\Api\Field\RequiredField;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Framework\Request\Interfaces\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;

class API_PostUserRecover extends API_Base {

    const FIELD_NAME = 'name';

    private $mUserTable;

    /**
     * Construct an instance of this API
     * @param \CPath\Framework\PDO\Templates\User\Model\PDOUserModel|\CPath\Framework\PDO\Templates\User\Table\PDOUserTable $UserTable the user source object for this API
     */
    function __construct(PDOUserTable $UserTable) {
        $this->mUserTable = $UserTable;
        parent::__construct($this->mUserTable);
    }

    protected function setupFields() {
        /** @var PDOUserModel $User  */
        //$User = $this->mUser;
        $this->addField(new RequiredField(self::FIELD_NAME, "User name or email"));
        //$this->generateFieldShorts();
    }

    /**
     * Get the Object Description
     * @return IDescribable|String a describable Object, or string describing this object
     */
    function getDescribable() {
        return "Recover " . $this->mUserTable;
    }

    /**
     * Execute this API Endpoint with the entire request.
     * @param IRequest $Request the IRequest instance for this render which contains the request and args
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final function execute(IRequest $Request) {
        //return new DataResponse("User password changed successfully", false, $User);
    }
}
