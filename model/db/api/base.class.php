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
use CPath\Interfaces\IRequest;
use CPath\Model\DB\Interfaces\IPDOModelRender;
use CPath\Model\DB\Interfaces\IReadAccess;
use CPath\Model\DB\Interfaces\ISecurityPolicy;
use CPath\Model\DB\Interfaces\ISecurityPolicyAggregate;
use CPath\Model\DB\Interfaces\SecurityPolicyNotFoundException;

abstract class API_Base extends API implements IDescribableAggregate {

    private $mHandlers = array();

    private $mModel;

    /**
     * Construct an instance of the GET API
     * @param PDOModel|IReadAccess $Model the user source object for this API
     * PRIMARY key is already included
     */
    function __construct(PDOModel $Model) {
        parent::__construct();
        $this->mModel = $Model;
        $this->mHandlers = array($this, $Model);
    }

    function addCallbackHandler($Object) {
        $this->mHandlers[] = $Object;
    }

    protected function getHandlers() {
        return $this->mHandlers;
    }

    /**
     * Set up API fields. Replaces setupAPI()
     * @return void
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     */
    abstract protected function setupFields();

    /**
     * Set up API fields. Lazy-loaded when fields are accessed
     * @return void
     * @throws InvalidAPIException if no PRIMARY key column or alternative columns are available
     * @throws SecurityPolicyNotFoundException if no security policy was found and ::SECURITY_DISABLED was not set for the model
     */
    final protected function setupAPI() {
        $this->setupFields();
        $Model = $this->mModel;
        if($Model::AUTO_SHORTS)
            $this->generateFieldShorts();

        $Policies = array();
        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof ISecurityPolicyAggregate)
                $this->mHandlers[] = $Policies[] = $Handler->getSecurityPolicy();
            elseif($Handler instanceof ISecurityPolicy)
                $Policies[] = $Handler;

        if(!$Policies) {
            $Model = $this->getModel();
            if($Model::SECURITY_DISABLED !== true)
                throw new SecurityPolicyNotFoundException("No security policy implemented for ".$Model->modelName() . "\n"
                    . "Security can be disabled with 'const SECURITY_DISABLED = true;'");
            $this->mHandlers[] = $Policies[] = new Policy_Public();
        }
    }


    /**
     * Get all security policies for this model
     * @return ISecurityPolicy[]
     * @throws SecurityPolicyNotFoundException if no policy is found and ::SECURITY_DISABLED !== true
     */
    function getSecurityPolicies() {
        $Policies = array();
        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof ISecurityPolicyAggregate)
                $Policies[] = $Handler->getSecurityPolicy();
            elseif($Handler instanceof ISecurityPolicy)
                $Policies[] = $Handler;

        if(!$Policies) {
            $Model = $this->getModel();
            if($Model::SECURITY_DISABLED !== true)
                throw new SecurityPolicyNotFoundException("No security policy implemented for ".$Model->modelName() . "\n"
                    . "Security can be disabled with 'const SECURITY_DISABLED = true;'");
            $Policies[] = new Policy_Public();
        }
        return $Policies;
    }

    /**
     * @return PDOModel
     */
    protected function getModel() {
        return $this->mModel;
    }

}
