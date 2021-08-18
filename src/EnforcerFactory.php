<?php

namespace Donjan\Casbin;

use Psr\Container\ContainerInterface;
use Casbin\Enforcer as BaseEnforcer;
use Casbin\Model\Model;
use Casbin\Log\Log;
use Hyperf\Logger\LoggerFactory;
use Casbin\Bridge\Logger\LoggerBridge;
use InvalidArgumentException;

class EnforcerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = config('casbin');
        if (is_null($config)) {
            throw new InvalidArgumentException("Enforcer config is not defined.");
        }

        if ($config['log']['enabled']) {
            $logger = $container->get(LoggerFactory::class)->get();
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
        $adapter = make($config['adapter']['class'], $config['adapter']['constructor']);
        return new BaseEnforcer($model, $adapter, $config['log']['enabled']);
    }

}
