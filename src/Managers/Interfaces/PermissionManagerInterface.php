<?php
namespace Morilog\Acl\Managers\Interfaces;

use Morilog\Acl\Models\Interfaces\PermissionInterface;

interface PermissionManagerInterface
{
    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions();

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $name
     * @return PermissionInterface
     */
    public function createPermissionByName($name);
}