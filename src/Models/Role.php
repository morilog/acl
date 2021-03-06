<?php

namespace Morilog\Acl\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Morilog\Acl\Models\Interfaces\PermissionInterface;
use Morilog\Acl\Models\Interfaces\RoleInterface;

class Role extends Model implements RoleInterface
{

    protected $table = 'roles';

    public $timestamps = false;

    protected $guarded = ['id'];


    public function users()
    {
        return $this->belongsToMany(app('config')->get('acl.user_model'), 'user_role', 'role_id', 'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(app('config')->get('acl.permission_model'), 'role_permission');
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getAttribute('title');
    }

    /**
     * @return Collection
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param PermissionInterface $permission
     * @return mixed
     */
    public function addPermission(PermissionInterface $permission)
    {
        return $this->permissions()->attach($permission->getId());
    }

    /**
     * @param Collection $permissions
     * @return mixed
     */
    public function addPermissions(Collection $permissions)
    {
        $permissionIds = $permissions->map(function ($perm) {
            return $perm->getId();
        })->toArray();

        return $this->permissions()->sync($permissionIds);
    }

    /**
     * @return Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
