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

class SecurityPolicyNotFoundException extends \Exception {}

interface ISecurityPolicy extends IReadAccess, IWriteAccess, IAssignAccess {

}