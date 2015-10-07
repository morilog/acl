<?php
namespace Morilog\Acl\Managers\Interfaces;

use Morilog\Acl\Models\Interfaces\RoleInterface;

interface RoleManagerInterface
{
    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $role
     * @return RoleInterface
     */
    public function getRoleByNameOrTitle($role);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $roleId
     * @return RoleInterface
     */
    public function getRoleById($roleId);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $role
     * @return RoleInterface
     * @throws ModelNotFoundException
     */
    public function getRole($role);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $roleName
     * @return RoleInterface
     */
    public function createRoleByName($roleName);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $role
     * @param $permission
     * @return mixed
     */
    public function addPermissionToRole($role, $permission);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $role
     * @param array $permissions
     * @return mixed
     */
    public function addPermissionsToRole($role, array $permissions);
}