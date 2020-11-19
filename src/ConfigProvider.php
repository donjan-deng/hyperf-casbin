<?php

namespace Donjan\Casbin;

use Donjan\Casbin\Models\Rule;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'commands' => [
                Commands\CacheClear::class
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
                ],
                [
                    'id' => 'database',
                    'description' => 'The database for casbin.',
                    'source' => __DIR__ . '/../database/migrations/create_rules_table.stub',
                    'destination' => BASE_PATH . '/migrations/2020_10_24_000000_create_rules_table.php',
                ]
            ],
        ];
    }

}
