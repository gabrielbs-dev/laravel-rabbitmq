<?php

namespace Gabrielbs\Rabbitmq;

class QueueChannel
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }
}