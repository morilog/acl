<?php
namespace Morilog\Acl;

use Morilog\Acl\Managers\Interfaces\RoleManagerInterface;
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
     * @param Role $roleModel
     */
    public function __construct(Role $roleModel)
    {
        $this->roleModel = $roleModel;
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
     * @param $permissions
     * @return mixed
     */
    public function addPermissionsToRole($role, $permissions)
    {
        // TODO: Implement addPermissionsToRole() method.
    }


}