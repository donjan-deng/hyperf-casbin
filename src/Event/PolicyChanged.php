<?php

declare(strict_types=1);

namespace Donjan\Casbin\Event;

class PolicyChanged
{

    protected $data = [];

    public function __construct(...$data)
    {
        $this->data = $data;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

}
