<?php

namespace Donjan\Casbin\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Hyperf\Database\Schema\Schema;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Donjan\Casbin\Enforcer;
use Mockery;
use function Hyperf\Support\env;

abstract class TestCase extends BaseTestCase
{

    protected $container;
    protected $config;
    protected $app;

    protected function setUp(): void
    {
        $this->container = ApplicationContext::getContainer();
        $this->container->define(\Casbin\Enforcer::class, \Donjan\Casbin\EnforcerFactory::class);
        $this->config = $this->container->get(ConfigInterface::class);
        $this->app = $this->container->get(ApplicationInterface::class);
        $this->initConfig();
        $this->initTable();
    }

    protected function initConfig()
    {
        $this->config->set('databases', [
            'default' => [
                'driver' => env('DB_DRIVER', 'mysql'),
                'host' => env('DB_HOST', 'localhost'),
                'database' => env('DB_DATABASE', 'hyperf'),
                'port' => env('DB_PORT', 3306),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
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
            'model' => [
                'config_type' => 'file',
                'config_file_path' => BASE_PATH . '/publish/casbin-rbac-model.conf',
                'config_text' => '',
            ],
            'adapter' => [
                'class' => \Donjan\Casbin\Adapters\Mysql\DatabaseAdapter::class,
                'constructor' => [
                    'tableName' => 'casbin_rule'
                ],
            ],
            'watcher' => [
                'enabled' => false,
                'class' => \Donjan\Casbin\Watchers\RedisWatcher::class,
                'constructor' => [
                    'channel' => 'casbin'
                ],
            ],
            'log' => [
                'enabled' => false,
            ]
        ]);
        $this->config->set('listeners', [
            \Donjan\Casbin\Listener\OnPipeMessageListener::class,
            \Donjan\Casbin\Listener\OnPolicyChangedListener::class
        ]);
    }

    protected function initTable()
    {
        Enforcer::getAdapter()->initTable();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists($this->config->get('casbin.adapter.constructor.tableName'));
        $this->delDir(BASE_PATH . '/runtime/container');
        Mockery::close();
    }

    public function delDir($path)
    {
        if (is_dir($path)) {
            $dirs = scandir($path);
            foreach ($dirs as $dir) {
                if ($dir != '.' && $dir != '..') {
                    $sonDir = $path . '/' . $dir;
                    if (is_dir($sonDir)) {
                        $this->delDir($sonDir);
                        @rmdir($sonDir);
                    } else {
                        @unlink($sonDir);
                    }
                }
            }
            @rmdir($path);
        }
    }
}
