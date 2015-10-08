<?php

return [
    /**
     * permissions cache key used in acl and cache drivers
     */
    'permissions_cache_key' => 'morilog_acl',

    'user_permissions_cache_key' => 'morilog_acl_users',

    /**
     * Default provided roles by Acl
     * please do not remove these
     */
    'default_roles' => [
        'admin',
        'editor',
        'regular',
        'guest',
    ],



    /**
     * Defaults roles that set to application new users
     */
    'user_default_roles' => [
        'regular',
    ],



    /**
     * Guest user must be has default permission in application non-restict areas
     */
    'guest_user_default_permissions' => [

    ],
    /**
     * Default models used by managers
     */
    'user_model' => \Morilog\Acl\Models\User::class,
    'role_model' => \Morilog\Acl\Models\Role::class,
    'permission_model' => \Morilog\Acl\Models\Permission::class,


    /**
     * add Admin user ID for add default roles to him/her
     */
    'admin_user_id' => null
];