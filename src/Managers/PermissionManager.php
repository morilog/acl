<?php
namespace Morilog\Acl\Managers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Morilog\Acl\Managers\Interfaces\PermissionManagerInterface;
use Morilog\Acl\Models\Interfaces\PermissionInterface;
use Morilog\Acl\Models\Permission;
use Morilog\ValueObjects\Slug;

class PermissionManager implements PermissionManagerInterface
{

    /**
     * @var Permission
     */
    private $permissionModel;

    public function __construct(Permission $permissionModel)
    {
        $this->permissionModel = $permissionModel;
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions()
    {
        return $this->permissionModel->all();
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $name
     * @param $title
     * @return PermissionInterface
     */
    public function createPermissionByNameAndTitle($name, $title = null)
    {
        return $this->permissionModel->create([
            'name' => (new Slug($name))->getValue(),
            'title' => $title
        ]);
    }

    /**
     * @param $permission
     * @return PermissionInterface
     */
    public function getPermission($permission)
    {
        if ($permission instanceof PermissionInterface) {
            return $permission;
        }

        if (is_string($permission)) {
            return $this->getPermissionByName($permission);
        }

        if (is_numeric($permission)) {
            return $this->getPermissionById($permission);
        }

        if (is_array($permission)) {
            if (isset($permission['name'])) {
                return $this->getPermissionByName($permission['name']);
            }
        }

        throw new ModelNotFoundException('Permission does not exists.');
    }

    /**
     * @param string $permissionName
     * @return PermissionInterface
     */
    public function getPermissionByName($permissionName)
    {
        return $this->permissionModel
            ->newQuery()
            ->where('name', $permissionName)
            ->first();
    }

    /**
     * @param int $permissionId
     * @return PermissionInterface
     */
    public function getPermissionById($permissionId)
    {
        return $this->permissionModel->findOrFail($permissionId);
    }

    /**
     * @param $name
     * @return bool
     */
    public function checkPermissionExistByName($name)
    {
        return $this->permissionModel
            ->newQuery()
            ->where('name', $name)
            ->exists();
    }

    /**
     * @return mixed
     */
    public function deleteAllPermissions()
    {
        return $this->permissionModel
            ->newQuery()
            ->delete();
    }


}