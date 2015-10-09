<?php
namespace Morilog\Acl\Models\Interfaces;

use Illuminate\Support\Collection;

interface RoleInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return Collection
     */
    public function getPermissions();

    /**
     * @param PermissionInterface $permission
     * @return mixed
     */
    public function addPermission(PermissionInterface $permission);

    /**
     * @param $permissions
     * @return mixed
     */
    public function addPermissions($permissions = []);

    /**
     * @return Collection
     */
    public function getUsers();


}