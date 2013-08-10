<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Model\DB\Interfaces;

use CPath\Interfaces\IRequest;
use CPath\Model\DB\InvalidPermissionException;
use CPath\Model\DB\PDOWhere;


interface ISecurityPolicyAggregate {
    /**
     * Create a security policy for this object
     * @return ISecurityPolicy the security policy
     */
    function getSecurityPolicy();
}