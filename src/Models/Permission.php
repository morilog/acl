<?php

namespace Morilog\Acl\Models;


use Morilog\Acl\Models\Interfaces\PermissionInterface;

class Permission extends BaseModel implements PermissionInterface
{

    protected $table = 'permissions';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $hidden = ['pivot'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getAttribute('title');
    }
}