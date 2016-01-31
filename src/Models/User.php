<?php

namespace Morilog\Acl\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Morilog\Acl\Models\Interfaces\RoleInterface;
use Morilog\Acl\Models\Interfaces\UserInterface;

class User extends Model implements Authenticatable, UserInterface
{

    use  \Illuminate\Auth\Authenticatable;

    /**
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(app('config')->get('acl.role_model'), 'user_role', 'user_id', 'role_id');
    }

    /**
     * Get user identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        if ($this->email !== null) {
            return $this->email;
        }

        return $this->username;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * @param RoleInterface $role
     * @return mixed
     */
    public function addRole(RoleInterface $role)
    {
        return $this->roles()->attach($role->getId());
    }

    /**
     * @return Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param Collection $roles
     * @param bool $detaching
     * @return mixed
     */
    public function addRoles(Collection $roles, $detaching = true)
    {
        $roleIds = $roles->map(function ($role) {
            return $role->getId();
        })->toArray();

        return $this->roles()->sync($roleIds, $detaching);
    }

    /**
     * @inheritdoc
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    public function getPermissionsAttribute()
    {
        $permissions = [];

        foreach ($this->getRoles() as $role) {
            foreach ($role->getPermissions() as $permission) {
                if (!in_array($permission, $permissions)) {
                    $permissions[] = $permission;
                }
            }
        }

        return collect($permissions);
    }
}
