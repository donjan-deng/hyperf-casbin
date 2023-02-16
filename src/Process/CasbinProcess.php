<?php

declare(strict_types=1);

namespace Donjan\Casbin\Process;

use Hyperf\Process\AbstractProcess;
use Psr\Container\ContainerInterface;
use Hyperf\Redis\Redis;
use Donjan\Casbin\Event\PipeMessage;
use Swoole\Server;

class CasbinProcess extends AbstractProcess
{

    /**
     * @var Server
     */
    protected $server;

    public function __construct(protected ContainerInterface $container)
    {
        $this->name = 'casbin-watcher';
        parent::__construct($container);
    }

    public function handle(): void
    {
        $redis = $this->container->get(Redis::class);
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);
        $channel = config('casbin.watcher.constructor.channel') ?? 'casbin';
        $redis->subscribe([$channel], function ($instance, $channel, $message) {
            $server = $this->server;
            $workerCount = $server->setting['worker_num'] + ($server->setting['task_worker_num'] ?? 0) - 1;
            for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                $server->sendMessage(new PipeMessage(PipeMessage::LOAD_POLICY), $workerId);
            }
        });
    }

    public function bind($server): void
    {
        $this->server = $server;
        parent::bind($server);
    }

    public function isEnable($server): bool
    {
        return config('casbin.watcher.enabled') == true;
    }

}
