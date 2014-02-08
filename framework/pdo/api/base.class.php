<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Framework\PDO;

use CPath\Framework\Api\Interfaces\InvalidAPIException;
use CPath\Framework\PDO\Interfaces\IReadAccess;
use CPath\Framework\PDO\Interfaces\ISecurityPolicy;
use CPath\Framework\PDO\Interfaces\ISecurityPolicyAggregate;
use CPath\Framework\PDO\Interfaces\SecurityPolicyNotFoundException;
use CPath\Framework\PDO\Table\PDOTable;
use CPath\Framework\Api\Types\AbstractAPI;
use CPath\Route\IRoute;
use CPath\Route\RoutableSet;

abstract class API_Base extends AbstractAPI {

    private $mHandlers = array();

    private $mTable;

    /**
     * Construct an instance of the GET API
     * @param PDOTable|IReadAccess $Table the PDOTable for this API
     * PRIMARY key is already included
     */
    function __construct(PDOTable $Table) {
        parent::__construct();
        $this->mTable = $Table;
        $this->mHandlers = array($this, $Table);
    }

    /**
     * Returns the route for this IHandler
     * @return IRoute|RoutableSet a new IRoute (typically a RouteableSet) instance
     */
    function loadRoute() {
        // TODO: Implement loadRoute() method.
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
//        $Table = $this->mTable;
//        if($Table::AUTO_SHORTS)
//            $this->generateFieldShorts();

        $Policies = array();
        foreach($this->getHandlers() as $Handler)
            if($Handler instanceof ISecurityPolicyAggregate)
                $this->mHandlers[] = $Policies[] = $Handler->getSecurityPolicy();
            elseif($Handler instanceof ISecurityPolicy)
                $Policies[] = $Handler;

        if(!$Policies) {
            $Table = $this->getTable();
            if($Table::SECURITY_DISABLED !== true)
                throw new SecurityPolicyNotFoundException("No security policy implemented for ".$Table->getModelName() . "\n"
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
            $Table = $this->getTable();
            if($Table::SECURITY_DISABLED !== true)
                throw new SecurityPolicyNotFoundException("No security policy implemented for ".$Table->getModelName() . "\n"
                    . "Security can be disabled with 'const SECURITY_DISABLED = true;'");
            $Policies[] = new Policy_Public();
        }
        return $Policies;
    }

    /**
     * @return PDOTable
     */
    protected function getTable() {
        return $this->mTable;
    }
}
