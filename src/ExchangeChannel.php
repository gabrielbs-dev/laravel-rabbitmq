<?php

namespace Gabrielbs\Rabbitmq;

class ExchangeChannel
{
    public $name;

    public $key;

    public function __construct(string $name, string $key = null)
    {
        $this->name = $name;
        $this->key = $key;
    }

    public function __toString()
    {
        return $this->name;
    }
}