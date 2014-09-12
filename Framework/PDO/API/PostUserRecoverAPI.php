<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO\API;


use CPath\Describable\IDescribable;
use CPath\Framework\API\Field\RequiredField;
use CPath\Framework\PDO\Templates\User\Model\PDOUserModel;
use CPath\Framework\PDO\Templates\User\Table\PDOUserTable;
use CPath\Request\IRequest;
use CPath\Framework\Response\Interfaces\IResponse;

class PostUserRecoverAPI extends AbstractPDOAPI {

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
     * @param Array $args additional arguments for this execution
     * @return IResponse|mixed the api call response with data, message, and status
     */
    final function execute(IRequest $Request, $args) {
        //return new DataResponse("User password changed successfully", false, $User);
    }
}
