<?php
namespace Morilog\Acl\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Routing\Router;
use Morilog\Acl\Managers\Interfaces\PermissionManagerInterface;

class AddPermissions extends Command
{
    protected $signature = 'morilog:acl:add-permissions';

    protected $description = 'Create pemrmissions from routes and save it in database & cache storage';

    /**
     * @var
     */
    private $router;

    /**
     * @var
     */
    private $permissionManager;

    /**
     * @var
     */
    private $cache;

    /**
     * @var
     */
    private $config;

    public function __construct(
        Router $router,
        PermissionManagerInterface $permissionManager,
        Repository $cache,
        ConfigRepository $config
    ) {

        $this->router = $router;
        $this->permissionManager = $permissionManager;
        $this->cache = $cache;
        $this->config = $config;
    }

    public function handle()
    {
        try {
            $routes = $this->router->getRoutes();
            $hasNewPermission = false;
            foreach ($routes as $route) {
                if (
                    $route->getName() !== null
                    && $this->permissionManager->checkPermissionExistByName($route->getName())
                ) {
                    $permission = $this->permissionManager->createPermissionByNameAndTitle($route->getName());

                    $hasNewPermission = true;

                    $this->info(sprintf('Permission %s has been added.', $permission->getName()));

                    unset($permission);
                }
            }



            if ($hasNewPermission === true) {

                $allPermissions = $this->permissionManager->getAllPermissions();

                $cacheKey = $this->config->get('acl.permissions_cache_key');

                $this->cache->forget($cacheKey);

                $this->cache->forever($cacheKey, $allPermissions);

                $this->info(sprintf('%d new Permissions has beed add successfully.', $allPermissions->count()));
            }



        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

}