<?php

namespace Donjan\Casbin;

use Casbin\Enforcer as BaseEnforcer;
use Casbin\Model\Model;
use Casbin\Log\Log;
use Casbin\Bridge\Logger\LoggerBridge;
use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use InvalidArgumentException;

/**
 * Enforcer
 * @method static array getRolesForUser(string $name, string ...$domain)
 * @method static array getUsersForRole(string $name, string ...$domain)
 * @method static bool hasRoleForUser(string $name, string $role, string ...$domain)
 * @method static bool addRoleForUser(string $user, string $role, string ...$domain)
 * @method static bool deleteRoleForUser(string $user, string $role, string ...$domain)
 * @method static bool deleteRolesForUser(string $user, string ...$domain)
 * @method static bool deleteUser(string $user)
 * @method static bool deleteRole(string $role)
 * @method static bool deletePermission(string ...$permission)
 * @method static bool addPermissionForUser(string $user, string ...$permission)
 * @method static bool deletePermissionForUser(string $user, string ...$permission)
 * @method static bool deletePermissionsForUser(string $user)
 * @method static array getPermissionsForUser(string $user)
 * @method static bool hasPermissionForUser(string $user, string ...$permission)
 * @method static array getImplicitRolesForUser(string $name, string ...$domain)
 * @method static array getImplicitPermissionsForUser(string $user, string ...$domain)
 * @method static array getImplicitUsersForPermission(string ...$permission)
 * @method static array getUsersForRoleInDomain(string $name, string $domain)
 * @method static array getRolesForUserInDomain(string $name, string $domain)
 * @method static array getPermissionsForUserInDomain(string $name, string $domain)
 * @method static bool addRoleForUserInDomain(string $user, string $role, string $domain)
 * @method static bool deleteRoleForUserInDomain(string $user, string $role, string $domain)
 */
class Enforcer
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The array of created "guards".
     *
     * @var array
     */
    protected $guards = [];

    /**
     * Create a new Enforcer instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Attempt to get the enforcer from the local cache.
     *
     * @param string $name
     *
     * @return \Casbin\Enforcer
     *
     * @throws \InvalidArgumentException
     */
    public function guard($name = null)
    {
        $name = $name ?: $this->getDefaultGuard();

        if (!isset($this->guards[$name])) {
            $this->guards[$name] = $this->resolve($name);
        }

        return $this->guards[$name];
    }

    /**
     * Resolve the given guard.
     *
     * @param string $name
     *
     * @return \Casbin\Enforcer
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = config('casbin.' . $name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Enforcer [{$name}] is not defined.");
        }

        if ($config['log']['enabled']) {
            $logger = $this->container->get(LoggerFactory::class)->get();
            Log::setLogger(new LoggerBridge($logger));
        }

        $model = new Model();
        $configType = $config['model']['config_type'];
        if ('file' == $configType) {
            $model->loadModel($config['model']['config_file_path']);
        } elseif ('text' == $configType) {
            $model->loadModelFromText($config['model']['config_text']);
        }
        if (!$config['adapter']['class']) {
            throw new InvalidArgumentException("Enforcer adapter is not defined.");
        }
        $adapter = make($config['adapter']['class']);
        return new BaseEnforcer($model, $adapter, $config['log']['enabled']);
    }

    /**
     * Get the default enforcer guard name.
     *
     * @return string
     */
    public function getDefaultGuard()
    {
        return 'default';
    }

    /**
     * call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->guard()->{$method}(...$parameters);
    }

    /**
     * call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return ApplicationContext::getContainer()->get(Enforcer::class)->{$method}(...$parameters);
    }

}
