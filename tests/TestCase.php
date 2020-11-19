<?php

namespace Donjan\Casbin\Tests;

require_once BASE_PATH . '/database/migrations/create_rules_table.stub';

use PHPUnit\Framework\TestCase as BaseTestCase;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Cache\Driver\FileSystemDriver;
use Hyperf\Utils\Packer\PhpSerializerPacker;
use Hyperf\Contract\ApplicationInterface;
use Mockery;

abstract class TestCase extends BaseTestCase
{

    protected $container;
    protected $config;
    protected $app;

    public function setUp()
    {
        $this->container = ApplicationContext::getContainer();
        $this->config = $this->container->get(ConfigInterface::class);
        $this->app = $this->container->get(ApplicationInterface::class);
        $this->initConfig();
        $this->initTable();
    }

    protected function initConfig()
    {
        $this->config->set('cache', [
            'default' => [
                'driver' => FileSystemDriver::class,
                'packer' => PhpSerializerPacker::class,
                'prefix' => 'c:',
            ],
        ]);
        $this->config->set('databases', [
            'default' => [
                'driver' => env('DB_DRIVER', 'mysql'),
                'host' => env('DB_HOST', 'localhost'),
                'database' => env('DB_DATABASE', 'hyperf'),
                'port' => env('DB_PORT', 3306),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => env('DB_CHARSET', 'utf8'),
                'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
                'prefix' => env('DB_PREFIX', ''),
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 10,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
                ]
            ]
        ]);
        $this->config->set('casbin', [
            'default' => [
                'model' => [
                    'config_type' => 'file',
                    'config_file_path' => BASE_PATH . '/publish/casbin-rbac-model.conf',
                    'config_text' => '',
                ],
                'adapter' => [
                    'class' => \Donjan\Casbin\Adapters\DatabaseAdapter::class,
                    'table_name' => 'casbin_rule',
                    'connection' => 'default'
                ],
                'log' => [
                    'enabled' => false,
                ],
                'cache' => [
                    'enabled' => false,
                    'key' => 'rules',
                    'ttl' => 24 * 60,
                ],
            ]
        ]);
    }

    protected function initTable()
    {
        $cmd = new \CreateRulesTable();
        $cmd->up();
    }

    public function tearDown()
    {
        $cmd = new \CreateRulesTable();
        $cmd->down();
        Mockery::close();
    }

}
