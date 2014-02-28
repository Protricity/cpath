<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/16/14
 * Time: 7:16 PM
 */
namespace CPath\Framework\PDO\Table\Column\Collection\Interfaces;

use CPath\Framework\Data\Collection\ICollection;
use CPath\Framework\PDO\Table\Column\Interfaces\IPDOColumn;

interface IPDOColumnCollection extends ICollection
{
    /**
     * Add an IRole to the collection
     * @param IPDOColumn $Column
     * @return IPDOColumnCollection return self
     */
    function add(IPDOColumn $Column);

//    /**
//     * Assert a user role and return the result as a boolean
//     * @param IRoleProfile $Profile the IRoleProfile to assert
//     * @return bool true if the role exists and the assertion passed
//     */
//    function has(IRoleProfile $Profile);
//
//    /**
//     * Calls ->assert on all roles in the collection
//     * @param IRoleProfile|null $Profile optional profile to assert
//     * @throws InvalidRoleException if the user role assertion fails
//     * @return void
//     */
//    function assert(IRoleProfile $Profile=null);
}