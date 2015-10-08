<?php
namespace Morilog\Acl\Managers\Interfaces;

interface UserManagerInterface
{
    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @param $roles
     * @return mixed
     * @throws \Morilog\Acl\ModelNotFoundException
     */
    public function addRolesToUser($user, $roles = []);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @param $role
     * @return mixed
     */
    public function addRoleToUser($user, $role);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $userId
     * @return mixed
     */
    public function getUserById($userId);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @return mixed
     */
    public function getUser($user);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @param $role
     * @return bool
     */
    public function userHasRole($user, $role);

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @param $roles
     * @return bool
     */
    public function userhasRoles($user, $roles = []);
}