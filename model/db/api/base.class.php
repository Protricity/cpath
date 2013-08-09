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
use CPath\Handlers\APIRequiredParam;
use CPath\Interfaces\IRequest;
use CPath\Interfaces\IResponse;
use CPath\Interfaces\InvalidAPIException;
use CPath\Model\DB\Interfaces\ILimitApiQuery;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\Response;

abstract class API_Base extends API {
    private $mModel;

    /**
     * Construct an instance of the GET API
     * @param PDOModel|IReadAccess $Model the user source object for this API
     * Primary key is already included
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    function __construct(PDOModel $Model) {
        parent::__construct();
        $this->mModel = $Model;
    }

    /**
     * @return PDOModel
     */
    protected function getModel() {
        return $this->mModel;
    }

}
