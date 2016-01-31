<?php

namespace Morilog\Acl\Models;


use Illuminate\Database\Eloquent\Model;
use Morilog\Acl\Models\Interfaces\PermissionInterface;

class Permission extends Model implements PermissionInterface
{

    protected $table = 'permissions';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $hidden = ['pivot'];

    public function roles()
    {
        return $this->belongsToMany(app('config')->get('acl.role_model'), 'role_permission');
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
