<?php

namespace Donjan\Casbin;

use Casbin\Enforcer as BaseEnforcer;
use Casbin\Model\Model;
use Casbin\Log\Log;
use Casbin\Bridge\Logger\LoggerBridge;
use Donjan\Casbin\Models\Rule;
use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use InvalidArgumentException;

/**
 * Enforcer
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
        $adapter = make($config['adapter']['class'], [
            'eloquent' => new Rule([], $name),
        ]);
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
