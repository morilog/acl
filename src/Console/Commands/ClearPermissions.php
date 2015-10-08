<?php
namespace Morilog\Acl\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Morilog\Acl\Managers\Interfaces\PermissionManagerInterface;

class ClearPermissions extends Command
{
    protected $signature = 'morilog:acl:clear-permissions';

    protected $description = '';

    /**
     * @var PermissionManagerInterface
     */
    private $permissionManager;

    /**
     * @var Repository
     */
    private $cache;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @param PermissionManagerInterface $permissionManager
     * @param Repository $cache
     * @param ConfigRepository $config
     */
    public function __construct(
        PermissionManagerInterface $permissionManager,
        Repository $cache,
        ConfigRepository $config
    ) {
        parent::__construct();

        $this->permissionManager = $permissionManager;
        $this->cache = $cache;
        $this->config = $config;
    }

    public function handle()
    {
        try {
            $configs = $this->config->get('acl');

            $this->permissionManager->deleteAllPermissions();

            $this->cache->forget($configs['acl.permission_cache_key']);
            $this->cache->tags($configs['acl.user_permissions_cache_key'])->flush();

            $this->info('All permissions are deleted from database and cache');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }



    }
}