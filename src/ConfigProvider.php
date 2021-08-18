<?php

namespace Donjan\Casbin;

use Donjan\Casbin\Listener\OnPipeMessageListener;
use Donjan\Casbin\Listener\OnPolicyChangedListener;
use Casbin\Enforcer;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Enforcer::class => EnforcerFactory::class,
            ],
            'listeners' => [
                OnPipeMessageListener::class,
                OnPolicyChangedListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for casbin.',
                    'source' => __DIR__ . '/../publish/casbin.php',
                    'destination' => BASE_PATH . '/config/autoload/casbin.php',
                ],
                [
                    'id' => 'model',
                    'description' => 'The model for casbin.',
                    'source' => __DIR__ . '/../publish/casbin-rbac-model.conf',
                    'destination' => BASE_PATH . '/config/autoload/casbin-rbac-model.conf',
                ]
            ],
        ];
    }

}
