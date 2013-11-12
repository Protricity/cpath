<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Model\DB;


use CPath\Handlers\API;
use CPath\Handlers\Api\Interfaces\InvalidAPIException;
use CPath\Interfaces\IDescribableAggregate;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\DB\Interfaces\ISecurityPolicy;
use CPath\Model\DB\Interfaces\ISecurityPolicyAggregate;
use CPath\Model\DB\Interfaces\SecurityPolicyNotFoundException;

abstract class API_Base extends API implements IDescribableAggregate {

    private $mModel, $mSecurity = NULL;

    /**
     * Construct an instance of the GET API
     * @param PDOModel|IReadAccess $Model the user source object for this API
     * PRIMARY key is already included
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    function __construct(PDOModel $Model) {
        parent::__construct();
        $this->mModel = $Model;
    }


    /**
     * Set up API fields. Replaces setupAPI()
     * @return void
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    abstract protected function setupAPIFields();

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    final protected function setupAPI() {
        $this->setupAPIFields();
        $Model = $this->mModel;
        if($Model::AUTO_SHORTS)
            $this->generateFieldShorts();
    }


    /**
     * Get security policy for this model
     * @return ISecurityPolicy
     * @throws SecurityPolicyNotFoundException if no policy is found and ::SECURITY_DISABLED !== true
     */
    function getSecurityPolicy() {
        if($this->mSecurity)
            return $this->mSecurity;
        $Model = $this->mModel;
        $Policy = $Model;
        if($Policy instanceof ISecurityPolicyAggregate)
            $Policy = $Policy->getSecurityPolicy();
        if(!$Policy instanceof ISecurityPolicy) {
            if($Model::SECURITY_DISABLED !== true)
                throw new SecurityPolicyNotFoundException("No security policy implemented for ".$Model->modelName() . "\n"
                    . "Security can be disabled with 'const SECURITY_DISABLED = true;'");
            $Policy = new Policy_Public();
        }
        return $this->mSecurity = $Policy;
    }

    /**
     * @return PDOModel
     */
    protected function getModel() {
        return $this->mModel;
    }

}
