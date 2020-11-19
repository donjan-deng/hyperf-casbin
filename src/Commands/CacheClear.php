<?php

declare(strict_types = 1);

namespace Donjan\Casbin\Commands;

use Donjan\Casbin\Models\Rule;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Utils\ApplicationContext;

class CacheClear extends HyperfCommand
{

    protected $name = 'casbin:cache-clear';

    public function __construct()
    {
        parent::__construct('casbin:cache-clear');
        $this->setDescription('Clear the casbin cache');
    }

    public function handle()
    {
        ApplicationContext::getContainer()->get(Rule::class)->forgetCache();
        $this->line('casbin cache flushed.');
    }

}
