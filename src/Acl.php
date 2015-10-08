<?php
namespace Morilog\Acl;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Router;
use Morilog\Acl\Exceptions\AclException;
use Morilog\Acl\Managers\Interfaces\PermissionManagerInterface;
use Morilog\Acl\Managers\Interfaces\RoleManagerInterface;
use Morilog\Acl\Managers\Interfaces\UserManagerInterface;
use Morilog\Acl\Models\Guest;
use Morilog\Acl\Models\Interfaces\UserInterface;

use Morilog\ValueObjects\Slug;

class Acl
{

    /**
     * @var Guard
     */
    private $auth;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var RoleManagerInterface
     */
    private $roleManager;

    /**
     * @var PermissionManagerInterface
     */
    private $permissionManager;

    /**
     * @var CacheRepository
     */
    private $cache;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Router
     */
    private $router;
    /**
     * @var User
     */
    private $user = null;

    /**
     * @param Guard $auth
     * @param UserManagerInterface $userManager
     * @param RoleManagerInterface $roleManager
     * @param PermissionManagerInterface $permissionManager
     * @param CacheRepository $cache
     * @param ConfigRepository $config
     * @param Router $router
     */
    public function __construct(
        Guard $auth,
        UserManagerInterface $userManager,
        RoleManagerInterface $roleManager,
        PermissionManagerInterface $permissionManager,
        CacheRepository $cache,
        ConfigRepository $config,
        Router $router
    ) {
        $this->auth = $auth;
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->permissionManager = $permissionManager;
        $this->cache = $cache;
        $this->config = $config->get('acl');
        $this->router = $router;
    }


    /**
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|User|null
     */
    protected function getUser()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if ($this->auth->check()) {
            return $this->auth->user();
        }

        return new Guest();
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }

    /**
     * @param UserManagerInterface $userManager
     */
    public function setUserManager($userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return RoleManagerInterface
     */
    public function getRoleManager()
    {
        return $this->roleManager;
    }

    /**
     * @param RoleManagerInterface $roleManager
     */
    public function setRoleManager($roleManager)
    {
        $this->roleManager = $roleManager;
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param UserInterface $user
     * @return \Illuminate\Support\Collection
     */
    protected function getUserPermissions(UserInterface $user)
    {
        $permissions = $this->getUserPermissionsFromCache($user);

        if (null === $permissions || empty($permissions->toArray())) {

            $permissions = $user->getPermissions();

            $this->cache
                ->tags($this->config['user_permissions_cache_key'])
                ->forever($this->getUserPermissionCacheKey($user), $permissions);
        }

        return $permissions;
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param UserInterface $user
     * @return string
     */
    protected function getUserPermissionCacheKey(UserInterface $user)
    {
        return 'permissions_' . sha1($user->getId());;
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param UserInterface $user
     * @param $key
     * @return array
     */
    protected function getUserPermissionsFromCache(UserInterface $user, $key)
    {
        return $this->cache
            ->tags('acl_users')
            ->get($this->getUserPermissionCacheKey($user));
    }


    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions()
    {
        $permissons = $this->getAllPermissionsFromCache($this->config['permissions_cache_key']);

        if ($permissons === null) {
            return $this->permissionManager->getAllPermissions();
        }

        return collect($permissons);
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $key
     * @return null|array
     */
    protected function getAllPermissionsFromCache($key)
    {
        return $this->cache->get($key);
    }

    /**
     * @param null $routeName
     * @return bool
     */
    public function hasAccess($routeName = null)
    {
        if ($routeName === null) {
            $routeName = $this->router->getCurrentRoute()->getName();

            if ($routeName === null) {
                return true;
            }
        }

        return $this->hasPermissionName($routeName);

    }

    /**
     * @param $permissionName
     * @return bool
     */
    public function hasPermissionName($permissionName)
    {
        $userPermissions = $this->getUserPermissions($this->getUser());

        foreach ($userPermissions as $permission) {
            if ($permission->name === $permissionName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $permissions
     * @return bool
     * @throws AclException
     */
    public function hasPermissions(array $permissions = [])
    {
        if (empty($permissions)) {
            throw new AclException('Permissions array is empty!');
        }

        $userPermissions = $this->getUserPermissions($this->getUser())->lists('name')->toArray();

        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $permissions
     * @return bool
     * @throws AclException
     */
    public function hasAnyOfPermissions(array $permissions = [])
    {
        if (empty($permissions)) {
            throw new AclException('Permissions array is empty!');
        }

        $userPermissions = $this->getUserPermissions($this->getUser())->lists('name')->toArray();

        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param null $resource
     * @return bool
     * @throws AclException
     */
    public function hasAccessToResource($resource = null)
    {
        if ($resource === null) {
            throw new AclException('Resource name must be provided');
        }

        $resourceName = explode('.', $resource)[0];

        $permissions = array_map(function ($item) use ($resourceName) {
            return $resourceName . '.' . $item;
        }, ['destroy', 'update', 'store']);

        return $this->hasPermissions($permissions);
    }

    /**
     * @param null $resource
     * @return bool
     * @throws AclException
     */
    public function hasAccessToAnyOfResource($resource = null)
    {
        if ($resource === null) {
            throw new AclException('Resource name must be provided');
        }

        $resourceName = explode('.', $resource)[0];

        $permissions = array_map(function ($item) use ($resourceName) {
            return $resourceName . '.' . $item;
        }, ['destroy', 'update', 'store']);

        return $this->hasAnyOfPermissions($permissions);
    }

    /**
     * @param $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->userManager->userHasRole($this->getUser(), $role);
    }

    public function hasRoleByName($roleName)
    {
        try {
            $role = $this->roleManager->getRoleByNameOrTitle($roleName);

            return $this->hasRole($role);
        } catch (ModelNotFoundException $e) {

            return false;
        }
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param array $roles
     * @return bool
     */
    public function hasRoles(array $roles)
    {
        return $this->userManager
            ->userHasRoles($this->getUser(), $roles);
    }

    /**
     * @param array $rolesName
     * @return bool
     * @throws AclException
     */
    public function hasRolesByName(array $rolesName)
    {
        return $this->userManager
            ->userHasRoles($this->getUser(), $rolesName);
    }

    /**
     * @author Morteza Parvini <m.parvini@outlook.com>
     * @param $role
     * @return mixed
     */
    public function addRole($role)
    {
        return $this->userManager
            ->addRoleToUser($this->getUser(),$this->roleManager->getRole($role));
    }

    /**
     * @param $roles
     * @return mixed
     * @throws AclException
     */
    public function addRoles($roles)
    {
        return $this->userManager->addRolesToUser($this->getUser(), $roles);
    }

    /**
     * @param $roleName
     * @return mixed
     * @throws AclException
     */
    public function addRoleByName($roleName)
    {
        try {
            $role = $this->roleManager->getRole($roleName);
        } catch (ModelNotFoundException $e) {
            $role = $this->roleManager->createRoleByName($roleName);
        }

        return $this->userManager->addRoleToUser($this->getUser(), $role);
    }

    public function addRolesByName(array $rolesName)
    {
        return $this->addRoles($rolesName);
    }

    /**
     * @param $role
     * @param $permission
     * @return mixed
     */
    public function addPermissionToRole($role, $permission)
    {
        return $this->roleManager->addPermissionToRole($role, $permission);
    }

    /**
     * @param $role
     * @param $permissionName
     * @return mixed
     */
    public function addPermissionToRoleByName($role, $permissionName)
    {
        try {
            $permission = $this->permissionManager->getPermission($permissionName);
        } catch (ModelNotFoundException $e) {
            $permission = $this->permissionManager->createPermissionByNameAndTitle($permissionName);
        }

        return $this->addPermissionToRole($role, $permission);
    }

    /**
     * @param Role $role
     * @param array $permissions
     * @return mixed
     */
    public function addPermissionsToRole($role, array $permissions)
    {
        $allPermissions = $this->getAllPermissions();

        foreach ($permissions as &$permission) {
            $permission = $this->permissionManager->getPermission($permission);
        }

        return $this->roleManager->addPermissionsToRole($role, $permissions);

    }

    /**
     * @param $role
     * @return bool
     */
    public function checkRoleIsDeletable($role)
    {
        $role = $this->roleManager->getRole($role);

        $defaultRoles = $this->config['default_roles'];

        return !in_array($role->getName(), $defaultRoles);
    }
}

