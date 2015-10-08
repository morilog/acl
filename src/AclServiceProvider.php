<?php
namespace Morilog\Acl;

use Illuminate\Support\ServiceProvider;
use Morilog\Acl\Managers\PermissionManager;

class AclServiceProvider extends ServiceProvider
{

    protected $defer = false;
    
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
        $this->mergeConfigFiles();

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

        $this->registerCommands();

    }


    private function registerManagers()
    {

        $this->app->singleton('Morilog\Acl\Managers\Interfaces\PermissionManagerInterface', function ($app){
            $permissionModel = $app[$app['config']->get('acl.permission_model')];

            return new PermissionManager($permissionModel);
        });


        $this->app->singleton('Morilog\Acl\Managers\Interfaces\RoleManagerInterface', function ($app) {
            $roleModel = $app[$app['config']->get('acl.role_model')];

            return new RoleManager(
                $roleModel,
                $app['Morilog\Acl\Managers\Interfaces\PermissionManagerInterface']
            );
        });

        $this->app->singleton('Morilog\Acl\Managers\Interfaces\UserManagerInterface', function ($app) {
            $userModel = $app[$app['config']->get('acl.user_model')];

            return new UserManager(
                $userModel,
                $app['Morilog\Acl\Managers\Interfaces\RoleManagerInterface']
            );
        });
    }


    private function mergeConfigFiles()
    {
        $packageConfigFile = __DIR__ . '/../config/acl.php';

        $this->mergeConfigFrom(
            $packageConfigFile, 'acl'
        );
    }


    private function registerCommands()
    {
        $this->commands([
            \Morilog\Acl\Console\Commands\AddDefaultRoles::class,
            \Morilog\Acl\Console\Commands\AddPermissions::class,
            \Morilog\Acl\Console\Commands\ClearPermissions::class,
            \Morilog\Acl\Console\Commands\AssignRolesToAdmin::class,
        ]);
    }
}