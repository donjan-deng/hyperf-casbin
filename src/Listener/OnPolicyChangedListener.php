<?php

declare(strict_types=1);

namespace Donjan\Casbin\Listener;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Contract\ConfigInterface;
use Donjan\Casbin\Event\PolicyChanged;
use Donjan\Casbin\Event\PipeMessage;
use Swoole\Server;
use Hyperf\Process\ProcessCollector;
use Hyperf\Server\ServerManager;

class OnPolicyChangedListener implements ListenerInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $this->container->get(ConfigInterface::class);
    }

    public function listen(): array
    {
        return [
            PolicyChanged::class,
        ];
    }

    public function process(object $event): void
    {
        if ($this->config->get('casbin.watcher.enabled')) { //启用watcher，不响应此事件
            return;
        }
        $serverManager = $this->container->get(ServerManager::class);
        if (count($serverManager::list()) > 0 && $event instanceof PolicyChanged) {
            $serverConfig = $this->container->get(ConfigInterface::class)->get('server', []);
            if (! $serverConfig) {
                throw new \InvalidArgumentException('At least one server should be defined.');
            }
            if ($serverConfig['type'] == Server::class) {
                $server = $this->container->get(Server::class);
                $workerCount = $server->setting['worker_num'] + ($server->setting['task_worker_num'] ?? 0) - 1;
                if ($workerCount > 0) {
                    for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                        if ($server->worker_id > -1 && $server->worker_id != $workerId) {
                            $server->sendMessage(new PipeMessage(PipeMessage::LOAD_POLICY), $workerId);
                        }
                    }
                }
            }
            if (class_exists(ProcessCollector::class) && !ProcessCollector::isEmpty()) {
                $processes = ProcessCollector::all();
                if ($processes) {
                    $string = serialize(new PipeMessage(PipeMessage::LOAD_POLICY));
                    foreach ($processes as $process) {
                        $process->exportSocket()->send($string, 10);
                    }
                }
            }
        }
    }
}
