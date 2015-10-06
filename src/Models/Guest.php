<?php
namespace Morilog\Acl\Models;

use Illuminate\Support\Collection;
use Morilog\Acl\Models\Interfaces\RoleInterface;
use Morilog\Acl\Models\Interfaces\UserInterface;

class Guest implements UserInterface
{
    /**
     * @return mixed
     */
    public function getId()
    {
        return 'guest';
    }

    /**
     * @param RoleInterface $role
     * @return mixed
     */
    public function addRole(RoleInterface $role)
    {
        return false;
    }

    /**
     * @return Collection
     */
    public function getRoles()
    {
        return Role::where('name', 'guest')->get();
    }

    /**
     * @param array $roles
     * @param bool $detaching
     * @return mixed
     */
    public function addRoles(array $roles, $detaching = true)
    {
        return true;
    }

    /**
     * @return Collection
     */
    public function getPermissions()
    {
        $roles = $this->getRoles();

        $permissions = collect([]);

        if ($roles->count() > 0) {
            $permissions = $roles->first()->getPermissions();
        }

        return $permissions;
    }
}