<?php
namespace Morilog\Acl\Services\Acl;

use Illuminate\Support\ServiceProvider;

class AclServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('moirlog.acl', function ($app) {
            return new AclManager(
                $app['auth.driver'],
                $app['Morilog\Acl\Repositories\Interfaces\UserRepositoryInterface'],
                $app['Morilog\Acl\Repositories\Interfaces\RoleRepositoryInterface'],
                $app['Morilog\Acl\Repositories\Interfaces\PermissionRepositoryInterface'],
                $app['cache.store'],
                $app['config'],
                $app['router']

            );
        });
    }
}