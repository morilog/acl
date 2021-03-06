<?php
namespace Morilog\Acl\Models\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interface UserInterface
 * @package Morilog\Acl\Models\Interfaces
 */
interface UserInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param RoleInterface $role
     * @return mixed
     */
    public function addRole(RoleInterface $role);

    /**
     * @return Collection
     */
    public function getRoles();

    /**
     * @param Collection $roles
     * @param bool $detaching
     * @return mixed
     */
    public function addRoles(Collection $roles, $detaching = true);

    /**
     * @return Collection
     */
    public function getPermissions();
}