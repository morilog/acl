<?php
namespace Morilog\Acl;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Morilog\Acl\Managers\Interfaces\RoleManagerInterface;
use Morilog\Acl\Managers\Interfaces\UserManagerInterface;
use Morilog\Acl\Models\Interfaces\RoleInterface;
use Morilog\Acl\Models\Interfaces\UserInterface;
use Morilog\Acl\Models\Role;
use Morilog\Acl\Models\User;

class UserManager implements UserManagerInterface
{

    /**
     * @var RoleManagerInterface
     */
    private $roleManager;

    /**
     * @var User
     */
    private $userModel;

    public function __construct(User $userModel, RoleManagerInterface $roleManager)
    {
        $this->roleManager = $roleManager;
        $this->userModel = $userModel;
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @param array $roles
     * @return mixed
     * @throws \Morilog\Acl\ModelNotFoundException
     */
    public function addRolesToUser($user, array $roles = [])
    {
        $user = $this->getUser($user);

        foreach ($roles as &$role) {
            $role = $this->roleManager->getRole($role);
        }

        return $user->addRoles($roles);
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @param $role
     * @return mixed
     */
    public function addRoleToUser($user, $role)
    {
        return $this->addRolesToUser($user, [$role]);
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $userId
     * @return mixed
     */
    public function getUserById($userId)
    {
        return $this->userModel
            ->newQuery()
            ->findOrFail($userId);
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @return UserInterface
     */
    public function getUser($user)
    {
        if ($user instanceof UserInterface) {
            return $user;
        }

        if (is_numeric($user)) {
            return $this->getUserById($user);
        }

        throw new ModelNotFoundException('User does not exist.');
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @param $role
     * @return bool
     */
    public function userHasRole($user, $role)
    {
        $user = $this->getUser($user);
        $role = $this->roleManager->getRole($role);

        return in_array(
            $role->getId(),
            $user->getRoles()->lists('id')->toArray()
        );
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $user
     * @param $roles
     * @return bool
     */
    public function userHasRoles($user, $roles = [])
    {
        $user = $this->getUser();

        $userRolesIds = $user->getRoles()->lists('id')->toArray();

        foreach ($roles as $role) {
            if (!in_array($this->roleManager->getRole($role), $userRolesIds)) {
                return false;
            }
        }

        return true;
    }

}