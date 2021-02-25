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

    protected function setUp(): void
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

    protected function tearDown(): void
    {
        $cmd = new \CreateRulesTable();
        $cmd->down();
        $this->delDir(BASE_PATH . '/runtime/container');
        Mockery::close();
    }

    public function delDir($path)
    {
        if (is_dir($path)) {
            //扫描一个目录内的所有目录和文件并返回数组
            $dirs = scandir($path);
            foreach ($dirs as $dir) {
                //排除目录中的当前目录(.)和上一级目录(..)
                if ($dir != '.' && $dir != '..') {
                    //如果是目录则递归子目录，继续操作
                    $sonDir = $path . '/' . $dir;
                    if (is_dir($sonDir)) {
                        //递归删除
                        $this->delDir($sonDir);
                        //目录内的子目录和文件删除后删除空目录
                        @rmdir($sonDir);
                    } else {
                        //如果是文件直接删除
                        @unlink($sonDir);
                    }
                }
            }
            @rmdir($path);
        }
    }

}
