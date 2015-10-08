<?php
namespace Morilog\Acl\Managers\Interfaces;

use Morilog\Acl\Models\Interfaces\PermissionInterface;

interface PermissionManagerInterface
{
    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions();

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $name
     * @param $title
     * @return PermissionInterface
     */
    public function createPermissionByNameAndTitle($name, $title = null);

    /**
     * @param $permission
     * @return PermissionInterface
     */
    public function getPermission($permission);

    /**
     * @param string $permissionName
     * @return PermissionInterface
     */
    public function getPermissionByName($permissionName);

    /**
     * @param int $permissionId
     * @return PermissionInterface
     */
    public function getPermissionById($permissionId);

    /**
     * @param $name
     * @return bool
     */
    public function checkPermissionExistByName($name);

    /**
     * @return mixed
     */
    public function deleteAllPermissions();

}