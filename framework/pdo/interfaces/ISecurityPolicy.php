<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ari
 * Date: 8/8/13
 * Time: 11:11 PM
 * To change this template use File | Settings | File Templates.
 */
namespace CPath\Framework\PDO\Interfaces;

class SecurityPolicyNotFoundException extends \Exception {}

interface ISecurityPolicy extends IReadAccess, IWriteAccess, IAssignAccess {

}