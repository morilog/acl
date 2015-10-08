<?php
namespace Morilog\Acl\Managers;

use Morilog\Acl\Managers\Interfaces\RoleManagerInterface;
use Morilog\Acl\Managers\Interfaces\PermissionManagerInterface;
use Morilog\Acl\Models\Interfaces\RoleInterface;
use Morilog\Acl\Models\Role;
use Morilog\ValueObjects\Slug;

class RoleManager implements RoleManagerInterface
{

    /**
     * @var Role
     */
    private $roleModel;
    /**
     * @var PermissionManagerInterface
     */
    private $permossionManager;

    /**
     * @param Role $roleModel
     * @param PermissionManagerInterface $permossionManager
     */
    public function __construct(Role $roleModel,  $permossionManager)
    {
        $this->roleModel = $roleModel;
        $this->permossionManager = $permossionManager;
    }

    /**
     * @inheritdoc
     */
    public function getRoleByNameOrTitle($role)
    {
        return $this->roleModel->newQuery()
            ->where('name', $role)
            ->orWhere('title', $role)
            ->findOrFail();
    }

    /**
     * @inheritdoc
     */
    public function getRoleById($roleId)
    {
        return $this->roleModel
            ->newQuery()
            ->findOrFail($roleId);
    }

    /**
     * @inheritdoc
     */
    public function getRole($role)
    {

        if ($role instanceof RoleInterface) {
            return $role;
        }

        if (is_numeric($role)) {
            return $this->getRoleById($role);
        }

        if (is_string($role)) {
            return $this->getRoleByNameOrTitle($role);
        }

        throw new ModelNotFoundException('Role does not exist.');

    }

    /**
     * @inheritdoc
     */
    public function createRoleByName($roleName)
    {
        return $this->roleModel->create([
            'name' => (new Slug($roleName))->getValue(),
            'title' => $roleName
        ]);
    }



    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $role
     * @param $permission
     * @return mixed
     */
    public function addPermissionToRole($role, $permission)
    {
        $role = $this->getRole($role);
        $permission = $this->permossionManager->getPermission($permission);

        return $role->addPermission($permission);
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $role
     * @param array $permissions
     * @return mixed
     */
    public function addPermissionsToRole($role, array $permissions)
    {
        $role = $this->getRole($role);

        foreach ($permissions as &$permission) {
            $permission = $this->permossionManager->getPermission($permission);
        }

        return $role->addPermissions($permissions);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllRoles()
    {
        return $this->roleModel->newQuery()->all();
    }
}