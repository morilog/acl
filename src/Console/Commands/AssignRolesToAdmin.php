<?php
namespace Morilog\Acl\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Morilog\Acl\Acl;

class AssignRolesToAdmin extends Command
{
    protected $signature = 'morilog:acl:admin-roles';

    protected $description = 'Add all roles to admn user';
    /**
     * @var Acl
     */
    private $acl;
    /**
     * @var Repository
     */
    private $config;

    public function __construct(Acl $acl, Repository $config)
    {
        parent::__construct();

        $this->acl = $acl;
        $this->config = $config;
    }

    public function handle()
    {
        try {
            $configs = $this->config->get('acl');

            $allRoles = $this->acl->getRoleManager()->getAllRoles();
            $adminUser = $this->acl->getUserManager()->getUserById($configs['admin_user_id']);

            $this->acl->setUser($adminUser)->addRoles($allRoles);

            $this->info('All roles assigned to admin user successfully.');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }
}