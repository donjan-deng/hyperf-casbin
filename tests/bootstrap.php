<?php

declare(strict_types = 1);

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Utils\ApplicationContext;

!defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));
!defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

Swoole\Runtime::enableCoroutine(true);

require BASE_PATH . '/vendor/autoload.php';

$container = new Container((new DefinitionSourceFactory(true))());
$container->set(ConfigInterface::class, $config = new Config([]));

ApplicationContext::setContainer($container);

$container->get(Hyperf\Contract\ApplicationInterface::class);
