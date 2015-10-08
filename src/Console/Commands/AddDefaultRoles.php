<?php
namespace Morilog\Acl\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Morilog\Acl\Managers\Interfaces\RoleManagerInterface;

class AddDefaultRoles extends Command
{
    protected $signature = 'morilog:acl:add-roles';

    protected $description = 'Insert default Roles to database.';

    /**
     * @var RoleManagerInterface
     */
    protected $roleManager;

    /**
     * @var Repository
     */
    private $config;

    public function __construct(RoleManagerInterface $roleManager, Repository $config)
    {
        $this->roleManger = $roleManager;
        $this->config = $config;
    }

    public function handle()
    {
        try {
            $defaultRoles = $this->config->get('acl.default_roles', []);

            foreach ($defaultRoles as $roleName) {
                $newRole = $this->roleManager->createRoleByName($roleName);

                $this->info(sprintf('Role %s has beed added successfully.', $newRole->getName()));
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }



    }

}