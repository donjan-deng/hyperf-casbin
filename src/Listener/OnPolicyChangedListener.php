<?php

namespace Donjan\Casbin\Listener;

use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Donjan\Casbin\Event\PolicyChanged;
use Donjan\Casbin\Event\PipeMessage;
use Swoole\Server;
use Hyperf\Process\ProcessCollector;

class OnPolicyChangedListener implements ListenerInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            PolicyChanged::class,
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof PolicyChanged) {
            $server = $this->container->get(Server::class);
            $workerCount = $server->setting['worker_num'] + ($server->setting['task_worker_num'] ?? 0) - 1;
            if ($workerCount > 0) {
                for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
                    $server->sendMessage(new PipeMessage(PipeMessage::LOAD_POLICY), $workerId);
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
