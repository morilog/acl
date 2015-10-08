<?php
namespace Morilog\Acl;

use Illuminate\Support\ServiceProvider;
use Morilog\Acl\Managers\PermissionManager;
use Morilog\Acl\Services\Acl\Facades\Acl;

class AclServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // Publish configs
        $this->publishes([
            __DIR__ . '/../config/acl.php' => config_path('acl.php')
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ .'/../migrations/' => database_path('migrations')
        ], 'migration');

    }

    public function register()
    {

        $this->registerManagers();

        $this->app->singleton('morilog.acl', function ($app) {
            return new Acl(
                $app['auth.driver'],
                $app['Morilog\Acl\Managers\Interfaces\UserManagerInterface'],
                $app['Morilog\Acl\Managers\Interfaces\RoleManagerInterface'],
                $app['Morilog\Acl\Managers\Interfaces\PermissionManagerInterface'],
                $app['cache.store'],
                $app['config'],
                $app['router']
            );
        });

    }

    private function registerManagers()
    {

        $this->app->bind('Morilog\Acl\Managers\Interfaces\PermissionManagerInterface', function ($app){
            $permissionModel = $app['config']->get('acl.permission_model');

            return new PermissionManager($permissionModel);
        });


        $this->app->bind('Morilog\Acl\Managers\Interfaces\RoleManagerInterface', function ($app) {
            $roleModel = $app['config']->get('acl.role_model');

            return new RoleManager(
                $roleModel,
                $app['Morilog\Acl\Managers\Interfaces\PermissionManagerInterface']
            );
        });

        $this->app->bind('Morilog\Acl\Managers\Interfaces\UserManagerInterface', function ($app) {
            $userModel = $app['config']->get('acl.user_model');

            return new UserManager(
                $userModel,
                $app['Morilog\Acl\Managers\Interfaces\RoleManagerInterface']
            );
        });
    }
}