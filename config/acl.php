<?php

return [
    'permissions_cache_key' => 'morilog_acl',
    'default_roles' => [
        'admin',
        'editor',
        'regular',
        'guest',
    ],
    'user_default_roles' => [
        'regular',
    ],
    'guest_user_default_permissions' => [

    ],
    'user_model' => \Morilog\Acl\Models\User::class,
    'role_model' => \Morilog\Acl\Models\Role::class,
    'permission_model' => \Morilog\Acl\Models\Permission::class,
];