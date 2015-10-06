<?php
namespace Morilog\Acl\Services\Acl;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Router;
use Morilog\Acl\Exceptions\AclException;
use Morilog\Acl\Models\Guest;
use Morilog\Acl\Models\Interfaces\PermissionInterface as Permission;
use Morilog\Acl\Models\Interfaces\PermissionInterface;
use Morilog\Acl\Models\Interfaces\RoleInterface as Role;
use Morilog\Acl\Models\Interfaces\RoleInterface;
use Morilog\Acl\Models\Interfaces\UserInterface as User;
use Morilog\Acl\Repositories\Interfaces\PermissionRepositoryInterface;
use Morilog\Acl\Repositories\Interfaces\RoleRepositoryInterface;
use Morilog\Acl\Repositories\Interfaces\UserRepositoryInterface;
use Morilog\Acl\ValueObjects\Slug;

class AclManager
{

    /**
     * @var Guard
     */
    private $auth;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepo;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepo;

    /**
     * @var PermissionRepositoryInterface
     */
    private $permissionRepo;

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
     * @param UserRepositoryInterface $userRepo
     * @param RoleRepositoryInterface $roleRepo
     * @param PermissionRepositoryInterface $permissionRepo
     * @param CacheRepository $cache
     * @param ConfigRepository $config
     * @param Router $router
     */
    public function __construct(
        Guard $auth,
        UserRepositoryInterface $userRepo,
        RoleRepositoryInterface $roleRepo,
        PermissionRepositoryInterface $permissionRepo,
        CacheRepository $cache,
        ConfigRepository $config,
        Router $router
    ) {
        $this->auth = $auth;
        $this->userRepo = $userRepo;
        $this->roleRepo = $roleRepo;
        $this->permissionRepo = $permissionRepo;
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

    public function getCertainUser()
    {
        if ($this->getUser() === null) {
            throw new AclException('User must be set.');
        }

        return $this->getUser();
    }

    /**
     * @param User $user
     * @return Collection
     */
    protected function getUserPermissions(User $user = null)
    {
        if ($user === null) {
            return [];
        }

        $permissionCacheKey = 'permissions_' . sha1($user->getId());

        $permissions = $this->cache->tags('acl_users')->get($permissionCacheKey);

        if (null === $permissions || empty($permissions->toArray())) {
            $permissions = $user->getPermissions();
            $this->cache->tags('acl_users')->forever($permissionCacheKey, $permissions);
        }

        return $permissions;
    }

    /**
     * @return Collection
     */
    public function getAllPermissions()
    {
        return collect($this->cache->get($this->config['permissions_cache_key'], []));
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
        if ($this->getUser() === null) {
            return false;
        }

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

        $userPermissions = $this->getUserPermissions($this->getUser())->lists('name');

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

        $userPermissions = $this->getUserPermissions($this->getUser())->lists('name');

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
     * @param Role $role
     * @return bool
     */
    public function hasRole(Role $role)
    {
        $roles = $this->getUser()->getRoles()->lists('id');

        return in_array($role->getId(), $roles);
    }

    public function hasRoleByName($roleName)
    {
        try {
            $role = $this->roleRepo->findOneBy('name', $roleName);

            return $this->hasRole($role);
        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param array $roles
     * @return bool
     * @throws AclException
     */
    public function hasRoles(array $roles)
    {
        $user = $this->getUser();

        $userRolesIds = $user->getRoles()->lists('id');

        foreach ($roles as $role) {
            if (!in_array($role->getId(), $userRolesIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $rolesName
     * @return bool
     * @throws AclException
     */
    public function hasRolesByName(array $rolesName)
    {
        $user = $this->getUser();

        $userRolesNames = $user->getRoles()->lists('name');

        foreach ($rolesName as $roleName) {
            if (!in_array($roleName, $userRolesNames)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Role $role
     * @return mixed
     * @throws AclException
     * @internal param User $user
     */
    public function addRole(Role $role)
    {
        $user = $this->getUser();

        return $this->userRepo->addRoleToUser($user, $role);
    }

    /**
     * @param $roleName
     * @return mixed
     * @throws AclException
     */
    public function addRoleByName($roleName)
    {
        try {
            $role = $this->roleRepo->findOneBy('name', $roleName);
        } catch (ModelNotFoundException $e) {
            $role = $this->roleRepo->create([
                'name' => (new Slug($roleName))->getValue(),
                'title' => $roleName
            ]);
        }

        return $this->addRole($role);
    }

    /**
     * @param array $roles
     * @return mixed
     * @throws AclException
     */
    public function addRoles(array $roles)
    {
        $user = $this->getUser();

        return $this->userRepo->addRolesToUser($user, $roles);
    }

    public function addRolesByName(array $rolesName)
    {
        $allRoles = $this->roleRepo->findAll();

        $roles = [];
        foreach ($rolesName as $name) {
            $role = $allRoles->where('name', $name)->first();

            if ($role !== null) {
                $roles[] = $role;
            }
        }

        return $this->addRoles($roles);
    }

    /**
     * @param Role $role
     * @param Permission $permission
     * @return mixed
     */
    public function addPermissionToRole(Role $role, Permission $permission)
    {
        return $this->roleRepo->addPermissionToRole($role, $permission);
    }

    /**
     * @param Role $role
     * @param $permissionName
     * @return mixed
     */
    public function addPermissionToRoleByName(Role $role, $permissionName)
    {
        try {
            $permission = $this->permissionRepo->findOneBy('name', $permissionName);
        } catch (ModelNotFoundException $e) {
            $permission = $this->permissionRepo->create([
                'name' => (new Slug($permissionName))->getValue()
            ]);
        }

        return $this->addPermissionToRole($role, $permission);
    }

    /**
     * @param Role $role
     * @param array $permissions
     * @return mixed
     */
    public function addPermissionsToRole(Role $role, array $permissions)
    {
        $allPermissions = $this->getAllPermissions();
        $acceptablePermissions = [];

        foreach ($permissions as $permission) {

            if ($permission instanceof PermissionInterface) {
                $acceptablePermissions[] = $permission;
                continue;
            }

            if (is_string($permission)) {
                if (in_array($permission, $allPermissions->lists('name'))) {
                    $acceptablePermissions[] = $allPermissions->where('name', $permission)->first();
                }
                continue;
            }

            if (is_numeric($permission)) {
                if (in_array($permission, $allPermissions->lists('id'))) {
                    $acceptablePermissions[] = $allPermissions->where('id', $permission)->first();
                }
            }

            if (is_array($permission)) {
                if (isset($permission['name']) && in_array($permission['name'], $allPermissions->lists('name'))) {
                    $acceptablePermissions[] = $allPermissions->where('name', $permission['name'])->first();
                }
                continue;
            }
        }

        return $this->roleRepo->addPermissionsToRole($role, $acceptablePermissions);

    }

    public function checkRoleIsDeletable($role)
    {

        if ($role instanceof RoleInterface) {
            $roleName = $role->getName();
        } else {
            if (is_numeric($role)) {
                $role = $this->roleRepo->findOneById($role);
                $roleName = $role->getName();
            } else {
                $roleName = $role;
            }
        }

        $defaultRoles = $this->config['default_roles'];

        return !in_array($roleName, $defaultRoles);
    }
}

