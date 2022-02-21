<?php

namespace Donjan\Casbin;

use Casbin\Effector\Effector;
use Casbin\Enforcer as BaseEnforcer;
use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\Watcher;
use Casbin\Rbac\RoleManager;
use Psr\Container\ContainerInterface;
use Hyperf\Utils\ApplicationContext;

/**
 * Enforcer
 * @method static void _loadFilteredPolicy($filter)
 * @method static void addFunction(string $name, \Closure $func)
 * @method static bool addGroupingPolicies(array $rules)
 * @method static bool addGroupingPolicy(...$params)
 * @method static bool addNamedDomainMatchingFunc(string $ptype, string $name, \Closure $fn)
 * @method static bool addNamedGroupingPolicies(string $ptype, array $rules)
 * @method static bool addNamedGroupingPolicy(string $ptype, ...$params)
 * @method static bool addNamedMatchingFunc(string $ptype, string $name, \Closure $fn)
 * @method static bool addNamedPolicies(string $ptype, array $rules)
 * @method static bool addNamedPolicy(string $ptype, ...$params)
 * @method static bool addPermissionForUser(string $user, string ...$permission)
 * @method static bool addPermissionsForUser(string $user, array ...$permissions)
 * @method static bool addPolicies(array $rules)
 * @method static bool addPolicy(...$params)
 * @method static bool addRoleForUser(string $user, string $role, string ...$domain)
 * @method static bool addRoleForUserInDomain(string $user, string $role, string $domain)
 * @method static bool addRolesForUser(string $user, array $roles, string ...$domain)
 * @method static array batchEnforce(array $requests)
 * @method static array batchEnforceWithMatcher(string $matcher, array $requests)
 * @method static void buildIncrementalRoleLinks(int $op, string $ptype, array $rules)
 * @method static void buildRoleLinks()
 * @method static void clearPolicy()
 * @method static bool deleteAllUsersByDomain(string $domain)
 * @method static bool deleteDomains(string ...$domains)
 * @method static bool deletePermission(string ...$permission)
 * @method static bool deletePermissionForUser(string $user, string ...$permission)
 * @method static bool deletePermissionsForUser(string $user)
 * @method static bool deleteRole(string $role)
 * @method static bool deleteRoleForUser(string $user, string $role, string ...$domain)
 * @method static bool deleteRoleForUserInDomain(string $user, string $role, string $domain)
 * @method static bool deleteRolesForUser(string $user, string ...$domain)
 * @method static bool deleteRolesForUserInDomain(string $user, string $domain)
 * @method static bool deleteUser(string $user)
 * @method static void enableAutoBuildRoleLinks(bool $autoBuildRoleLinks = true)
 * @method static void enableAutoNotifyWatcher(bool $enabled = true)
 * @method static void enableAutoSave(bool $autoSave = true)
 * @method static void enableEnforce(bool $enabled = true)
 * @method static void enableLog(bool $enabled = true)
 * @method static bool enforce(...$rvals)
 * @method static array enforceEx(...$rvals)
 * @method static bool enforceWithMatcher(string $matcher, ...$rvals)
 * @method static ?Adapter getAdapter()
 * @method static array getAllActions()
 * @method static array getAllNamedActions(string $ptype)
 * @method static array getAllNamedObjects(string $ptype)
 * @method static array getAllNamedRoles(string $ptype)
 * @method static array getAllNamedSubjects(string $ptype)
 * @method static array getAllObjects()
 * @method static array getAllRoles()
 * @method static array getAllSubjects()
 * @method static array getAllUsersByDomain(string $domain)
 * @method static array getFilteredGroupingPolicy(int $fieldIndex, string ...$fieldValues)
 * @method static array getFilteredNamedGroupingPolicy(string $ptype, int $fieldIndex, string ...$fieldValues)
 * @method static array getFilteredNamedPolicy(string $ptype, int $fieldIndex, string ...$fieldValues)
 * @method static array getFilteredPolicy(int $fieldIndex, string ...$fieldValues)
 * @method static array getGroupingPolicy()
 * @method static array getImplicitPermissionsForUser(string $user, string ...$domain)
 * @method static array getImplicitResourcesForUser(string $user, string ...$domain)
 * @method static array getImplicitRolesForUser(string $name, string ...$domain)
 * @method static array getImplicitUsersForPermission(string ...$permission)
 * @method static array getImplicitUsersForRole(string $name, string ...$domain)
 * @method static Model getModel()
 * @method static array getNamedGroupingPolicy(string $ptype)
 * @method static array getNamedPolicy(string $ptype)
 * @method static array getPermissionsForUser(string $user, string ...$domain)
 * @method static array getPermissionsForUserInDomain(string $name, string $domain)
 * @method static array getPolicy()
 * @method static RoleManager getRoleManager()
 * @method static array getRolesForUser(string $name, string ...$domain)
 * @method static array getRolesForUserInDomain(string $name, string $domain)
 * @method static array getUsersForRole(string $name, string ...$domain)
 * @method static array getUsersForRoleInDomain(string $name, string $domain)
 * @method static bool hasGroupingPolicy(...$params)
 * @method static bool hasNamedGroupingPolicy(string $ptype, ...$params)
 * @method static bool hasNamedPolicy(string $ptype, ...$params)
 * @method static bool hasPermissionForUser(string $user, string ...$permission)
 * @method static bool hasPolicy(...$params)
 * @method static bool hasRoleForUser(string $name, string $role, string ...$domain)
 * @method static void initRmMap()
 * @method static void initWithAdapter(string $modelPath, Adapter $adapter)
 * @method static void initWithFile(string $modelPath, string $policyPath)
 * @method static void initWithModelAndAdapter(Model $m, Adapter $adapter = null)
 * @method static bool isFiltered()
 * @method static void loadFilteredPolicy($filter)
 * @method static void loadIncrementalFilteredPolicy($filter)
 * @method static void loadModel()
 * @method static void loadPolicy()
 * @method static bool removeFilteredGroupingPolicy(int $fieldIndex, string ...$fieldValues)
 * @method static bool removeFilteredNamedGroupingPolicy(string $ptype, int $fieldIndex, string ...$fieldValues)
 * @method static bool removeFilteredNamedPolicy(string $ptype, int $fieldIndex, string ...$fieldValues)
 * @method static bool removeFilteredPolicy(int $fieldIndex, string ...$fieldValues)
 * @method static bool removeGroupingPolicies(array $rules)
 * @method static bool removeGroupingPolicy(...$params)
 * @method static bool removeNamedGroupingPolicies(string $ptype, array $rules)
 * @method static bool removeNamedGroupingPolicy(string $ptype, ...$params)
 * @method static bool removeNamedPolicies(string $ptype, array $rules)
 * @method static bool removeNamedPolicy(string $ptype, ...$params)
 * @method static bool removePolicies(array $rules)
 * @method static bool removePolicy(...$params)
 * @method static void savePolicy()
 * @method static void setAdapter(Adapter $adapter)
 * @method static void setEffector(Effector $eft)
 * @method static void setModel(Model $model)
 * @method static void setRoleManager(RoleManager $rm)
 * @method static void setWatcher(Watcher $watcher)
 * @method static bool updateFilteredNamedPolicies(string $ptype, array $newPolicies, int $fieldIndex, string ...$fieldValues)
 * @method static bool updateFilteredPolicies(array $newPolicies, int $fieldIndex, string ...$fieldValues)
 * @method static bool updateNamedPolicies(string $ptype, array $oldPolices, array $newPolicies)
 * @method static bool updateNamedPolicy(string $ptype, array $oldRule, array $newRule)
 * @method static bool updatePolicies(array $oldPolices, array $newPolicies)
 * @method static bool updatePolicy(array $oldRule, array $newRule)
 */
class Enforcer
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return ApplicationContext::getContainer()->get(BaseEnforcer::class)->{$method}(
            ...
            $parameters
        );
    }

    public function __call($method, $parameters)
    {
        return $this->container->get(BaseEnforcer::class)->{$method}(...$parameters);
    }

}
